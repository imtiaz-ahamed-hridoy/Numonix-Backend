{{-- -----------------------------------------------------------------------------
     Learning editor · client-side draft autosave & restore
     -----------------------------------------------------------------------------
     Included by _form.blade.php inside its 'scripts' push, after the editor's
     own script. The module body is a jQuery ready callback, not a bare IIFE:
     _form assigns window.learningEditor inside its own ready callback, which
     jQuery defers to DOMContentLoaded, and ready callbacks run in registration
     order - so by the time this one runs, the API exists. The module is purely
     additive: it snapshots the form into localStorage while the author works and
     offers to restore that snapshot on the next visit. It never alters the POST,
     never calls preventDefault on submit, and if localStorage is unavailable it
     becomes a silent no-op.

     Keys (all under the numonix:learningDraft: prefix, see the prune sweep):
       <draftKey>            this lesson's draft - one per admin user per lesson
       <draftKey>:previous   copy stashed by "Decide later" so a fresh edit
                             cannot clobber it
       v1:pending            -> the <draftKey> most recently submitted; the
                             learning index clears it when the success flash
                             renders (signal B), the editor clears it when the
                             server content version has moved on (signal A) --}}
<script>
jQuery(function($) {
    'use strict';

    var api = window.learningEditor;
    var $form = $('#learningForm');
    if (!api || !$form.length || !api.draftKey) return;

    // --- Constants -----------------------------------------------------------
    var PREFIX         = 'numonix:learningDraft:';
    var SCHEMA         = 1;
    var KEY            = api.draftKey;
    var PREVIOUS_KEY   = KEY + ':previous';
    var PENDING_KEY    = PREFIX + 'v1:pending';
    var SERVER_VERSION = api.contentVersion || null;   // null on the create form
    var RETENTION_MS   = 14 * 24 * 60 * 60 * 1000;
    var DEBOUNCE_MS    = 1000;
    var MAX_WAIT_MS    = 5000;
    var SETTLE_MS      = 150;   // just after the editor's own 50 ms / 100 ms boot timers
    var LARGE_BYTES    = 2 * 1024 * 1024;

    var META_FIELDS = ['category', 'title', 'subtitle', 'slug', 'read_time', 'group_id',
                       'status', 'is_published', 'is_premium', 'guest_access_override'];

    // Element types whose whole data object lives in a hidden JSON textarea that
    // serializeAllForSubmit() keeps current. Its parsed value is exactly the shape
    // the matching init*() reads back, so it replaces the generic lift wholesale.
    var JSON_GRID_FIELD = {
        table:                  'table_json',
        sentence_visual:        'sentence_json',
        template_form_table:    'template_form_table_json',
        movement_quality_table: 'movement_quality_table_json',
        centre_character_table: 'centre_character_table_json',
        functional_flow:        'functional_flow_json',
        exploring_section:      'exploring_section_json',
        centre_identity_card:   'centre_identity_card_json',
        form_identity_card:     'form_identity_card_json'
    };
    var MEDIA_TYPES = ['image', 'transparent_image', 'audio'];

    // --- Storage (every access guarded; no storage -> no module) --------------
    var storage = (function() {
        try {
            var s = window.localStorage;
            var probe = PREFIX + '__probe';
            s.setItem(probe, '1');
            s.removeItem(probe);
            return s;
        } catch (e) {
            return null;
        }
    })();
    if (!storage) return;

    function getItem(key) {
        try { return storage.getItem(key); } catch (e) { return null; }
    }
    function removeItem(key) {
        try { storage.removeItem(key); } catch (e) { /* ignore */ }
    }
    function trySetItem(key, value) {
        try { storage.setItem(key, value); return true; } catch (e) { return false; }
    }
    function allDraftKeys() {
        var keys = [];
        try {
            for (var i = 0; i < storage.length; i++) {
                var key = storage.key(i);
                if (key && key.indexOf(PREFIX) === 0) keys.push(key);
            }
        } catch (e) { /* ignore */ }
        return keys;
    }
    function parseDraft(raw) {
        if (!raw) return null;
        try {
            var draft = JSON.parse(raw);
            if (!draft || typeof draft !== 'object' || typeof draft.savedAt !== 'number') return null;
            return draft;
        } catch (e) {
            return null;
        }
    }
    function isExpired(draft) {
        return Date.now() - draft.savedAt > RETENTION_MS;
    }
    // A usable draft for this editor: right schema, not expired.
    function readDraft(key) {
        var draft = parseDraft(getItem(key));
        if (!draft || draft.v !== SCHEMA || isExpired(draft)) return null;
        return draft;
    }
    function pruneExpired() {
        allDraftKeys().forEach(function(key) {
            if (key === PENDING_KEY) return;
            var draft = parseDraft(getItem(key));
            if (draft && isExpired(draft)) removeItem(key);
        });
    }
    // Quota relief: drop other lessons' drafts oldest-first, retrying after each.
    function pruneOtherDraftsOldestFirst(retry) {
        var others = allDraftKeys()
            .filter(function(key) { return key !== KEY && key !== PREVIOUS_KEY && key !== PENDING_KEY; })
            .map(function(key) {
                var draft = parseDraft(getItem(key));
                return { key: key, savedAt: draft ? draft.savedAt : Infinity };
            })
            .sort(function(a, b) { return a.savedAt - b.savedAt; });
        for (var i = 0; i < others.length; i++) {
            removeItem(others[i].key);
            if (retry()) return true;
        }
        return false;
    }

    // --- Notifications -------------------------------------------------------
    function notify(level, message) {
        if (window.toastr && typeof window.toastr[level] === 'function') {
            window.toastr[level](message);
        } else if (level !== 'success') {
            console.warn('[learning draft] ' + message);
        }
    }
    function formatWhen(ts) {
        try { return new Date(ts).toLocaleString(); } catch (e) { return String(ts); }
    }
    function escapeText(value) {
        return String(value).replace(/[&<>"']/g, function(c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
        });
    }

    // --- Snapshot: form -> initialPages-shaped object -------------------------
    function fieldValue($el) {
        var value = $el.val();
        return value == null ? '' : value;
    }
    function toNumberIfNumeric(value) {
        return (typeof value === 'string' && /^\d+$/.test(value)) ? Number(value) : value;
    }
    // Mirrors ElementDataNormalizer::linesToArray().
    function linesToArray(text) {
        return String(text == null ? '' : text)
            .split(/\r\n|\r|\n/)
            .map(function(line) { return line.trim(); })
            .filter(function(line) { return line !== ''; });
    }
    // The server drops <img> and never keeps data: URIs (RICH_TEXT_ALLOWED_TAGS), so
    // removing them here loses nothing and keeps a pasted screenshot out of storage.
    function stripHeavyHtml(html) {
        if (typeof html !== 'string') return html;
        return html
            .replace(/<img\b[^>]*>/gi, '')
            .replace(/\s+(?:src|href|srcset|poster|background)\s*=\s*(["'])\s*data:[\s\S]*?\1/gi, '');
    }

    function collectElement($el, pageNo, elementNo, lostFiles) {
        var type = fieldValue($el.children('input[name$="[type]"]').first());
        var element = { type: type, data: {} };

        var id = fieldValue($el.children('input[name$="[id]"]').first());
        if (id !== '') element.id = toNumberIfNumeric(id);
        var position = fieldValue($el.find('.element-position-input').first());
        if (position !== '') element.position = toNumberIfNumeric(position);

        // Generic lift: every named [data][<field>] control; a trailing [] collects
        // into an array, exactly as PHP would receive it.
        $el.find('input, select, textarea').each(function() {
            var name = this.getAttribute('name') || '';
            var match = /\[data\]\[([^\]]+)\](\[\])?$/.exec(name);
            if (!match || this.type === 'file') return;
            if ((this.type === 'checkbox' || this.type === 'radio') && !this.checked) return;
            var value = fieldValue($(this));
            if (match[2]) {
                if (!Array.isArray(element.data[match[1]])) element.data[match[1]] = [];
                element.data[match[1]].push(value);
            } else {
                element.data[match[1]] = value;
            }
        });

        // Per-type fix-ups so `data` is the shape elementFields() / init*() read.
        var gridField = JSON_GRID_FIELD[type];
        if (gridField) {
            var raw = fieldValue($el.find('textarea[name$="[data][' + gridField + ']"]').first());
            var parsed = null;
            try { parsed = raw ? JSON.parse(raw) : null; } catch (e) { parsed = null; }
            element.data = (parsed && typeof parsed === 'object') ? parsed : {};
        } else if (type === 'text') {
            element.data.text = stripHeavyHtml(element.data.text);
        } else if (type === 'bullet_list') {
            element.data.items = (Array.isArray(element.data.items) ? element.data.items : []).map(stripHeavyHtml);
        } else if (type === 'checklist') {
            element.data.items = linesToArray(element.data.items_text);
            delete element.data.items_text;
        } else if (type === 'defining_behaviours_card') {
            element.data.bullets = linesToArray(element.data.bullets_text);
            delete element.data.bullets_text;
            element.data.best_for = {
                label: element.data.best_for_label != null ? element.data.best_for_label : 'Best for:',
                text:  element.data.best_for_text  != null ? element.data.best_for_text  : ''
            };
            delete element.data.best_for_label;
            delete element.data.best_for_text;
        } else if (MEDIA_TYPES.indexOf(type) !== -1) {
            element.file_url = fieldValue($el.find('input[name$="[existing_file_url]"]').first()) || null;
            var $link = $el.find('a[target="_blank"]').filter(function() {
                return $(this).text().indexOf('View current file') !== -1;
            }).first();
            element.public_file_url = $link.length ? $link.attr('href') : null;
        }

        // A File object cannot be persisted; remember where one was selected.
        $el.find('input[type="file"]').each(function() {
            if (this.files && this.files.length) {
                var label = $el.find('.badge').first().text().trim() || type;
                lostFiles.push('Page ' + pageNo + ' > ' + label + ' #' + elementNo);
            }
        });

        return element;
    }

    function collectSnapshot() {
        api.serializeAllForSubmit();

        var lostFiles = [];
        var meta = {};
        META_FIELDS.forEach(function(field) {
            meta[field] = fieldValue($form.find('[name="' + field + '"]').first());
        });
        meta.summary = fieldValue($('#summaryHidden'));

        var thumbnail = $form.find('input[type="file"][name="thumbnail"]')[0];
        if (thumbnail && thumbnail.files && thumbnail.files.length) lostFiles.push('Thumbnail');

        var pages = [];
        $('#pagesContainer .page-block').each(function(pageIdx) {
            var $page = $(this);
            var page = {
                title:         fieldValue($page.find('input.page-title-input').first()),
                section_title: fieldValue($page.find('input.page-section-title-input').first()),
                elements:      []
            };
            var pageId = fieldValue($page.children('input[name$="[id]"]').first());
            if (pageId !== '') page.id = toNumberIfNumeric(pageId);

            $page.find('.elements-container').first().children('.element-block').each(function(elementIdx) {
                page.elements.push(collectElement($(this), pageIdx + 1, elementIdx + 1, lostFiles));
            });
            pages.push(page);
        });

        // serializeAllForSubmit() -> reindexAll() rewrites the page-number spans
        // with .text(), which the observer below sees as childList mutations.
        // Drop those records now so a collect never re-schedules itself.
        if (observer) observer.takeRecords();

        return {
            v: SCHEMA,
            key: KEY,
            savedAt: Date.now(),
            submittedAt: null,
            baseVersion: SERVER_VERSION,
            meta: meta,
            pages: pages,
            lostFiles: lostFiles
        };
    }

    // Content-only identity: two snapshots with the same fingerprint would POST
    // the same lesson, regardless of when they were taken.
    function fingerprintOf(snap) {
        return JSON.stringify({ meta: snap.meta, pages: snap.pages, lostFiles: snap.lostFiles });
    }

    // --- Persistence ---------------------------------------------------------
    var bootFingerprint = null;   // the server state this page loaded with
    var lastFingerprint = null;   // what storage (or memory) currently holds
    var memorySnapshot  = null;   // last snapshot when storage is full
    var quotaWarned     = false;
    var sizeWarned      = false;

    // Writes to KEY, working down the quota ladder. Returns true once stored.
    function persist(snap, fingerprint) {
        var json;
        try { json = JSON.stringify(snap); } catch (e) { return false; }

        if (json.length > LARGE_BYTES && !sizeWarned) {
            sizeWarned = true;
            notify('warning', 'This lesson\'s draft is very large (~' + (json.length / 1048576).toFixed(1) +
                ' MB). Autosave still works, but save often.');
        }

        var attempt = function() { return trySetItem(KEY, json); };
        if (attempt() || pruneOtherDraftsOldestFirst(attempt)) {
            lastFingerprint = fingerprint;
            memorySnapshot = null;
            return true;
        }
        removeItem(PREVIOUS_KEY);
        if (attempt()) {
            lastFingerprint = fingerprint;
            memorySnapshot = null;
            return true;
        }

        memorySnapshot = snap;
        lastFingerprint = fingerprint;
        if (!quotaWarned) {
            quotaWarned = true;
            notify('warning', 'Draft autosave unavailable (browser storage full) - save often');
        }
        return false;
    }

    // Stores the snapshot if it differs from the last write; a snapshot that
    // matches the server state means nothing is unsaved, so the draft is dropped.
    function writeDraft(snap, force) {
        var fingerprint = fingerprintOf(snap);
        if (!force && fingerprint === lastFingerprint) return fingerprint;
        if (fingerprint === bootFingerprint) {
            removeItem(KEY);
            lastFingerprint = fingerprint;
            memorySnapshot = null;
            return fingerprint;
        }
        persist(snap, fingerprint);
        return fingerprint;
    }

    // --- Autosave wiring -----------------------------------------------------
    var armed    = false;
    var inFlight = false;
    var timer    = null;
    var maxTimer = null;
    var observer = null;

    function clearTimers() {
        clearTimeout(timer);
        clearTimeout(maxTimer);
        timer = maxTimer = null;
    }
    function flush() {
        clearTimers();
        if (!armed || inFlight) return;
        try {
            writeDraft(collectSnapshot(), false);
        } catch (e) {
            console.error('[learning draft] autosave failed', e);
        }
    }
    function scheduleSnapshot() {
        if (!armed || inFlight) return;
        clearTimeout(timer);
        timer = setTimeout(flush, DEBOUNCE_MS);
        if (!maxTimer) maxTimer = setTimeout(flush, MAX_WAIT_MS);
    }
    function arm() {
        if (armed) return;
        armed = true;

        // Typed edits, select/checkbox changes, and Summernote's own change event
        // (a jQuery-triggered event on the textarea, so it bubbles to the form).
        $form.on('input change summernote.change', scheduleSnapshot);

        // Structural edits: add/remove/move page or element, grid re-renders -
        // including the Swal-confirmed removals whose confirming click happens
        // outside the form.
        var container = document.getElementById('pagesContainer');
        if (container && window.MutationObserver) {
            observer = new MutationObserver(scheduleSnapshot);
            observer.observe(container, { childList: true, subtree: true });
        }

        $(document).on('visibilitychange', function() {
            if (document.visibilityState === 'hidden') flush();
        });
        $(window).on('pagehide', flush);
    }

    // --- Restore -------------------------------------------------------------
    function setField(name, value) {
        var $field = $form.find('[name="' + name + '"]').first();
        if ($field.length) $field.val(value == null ? '' : String(value));
    }
    // Mirrors the editor's own #category change handler.
    function toggleGroupField(category) {
        if (category === 'case_study' || category === 'fnh_architecture') {
            $('#groupField').show();
        } else {
            $('#groupField').hide();
        }
    }

    function applySnapshot(snap) {
        var meta = snap.meta || {};

        // Category first: it drives getActiveLabels() for every element-type dropdown.
        setField('category', meta.category);
        api.refreshElementTypeDropdowns();
        api.filterGroupsByCategory();
        toggleGroupField(meta.category);
        META_FIELDS.forEach(function(field) {
            if (field !== 'category') setField(field, meta[field]);
        });

        api.loadTakeaways(meta.summary || '');
        api.resetBuilder();
        (snap.pages || []).forEach(function(page) { api.addPage(page); });
        api.reindexAll();
        api.refreshEmptyState();
        setTimeout(function() { api.initPendingGrids(); }, 50);

        if (snap.lostFiles && snap.lostFiles.length) {
            notify('warning', 'Re-select these files before saving: ' + snap.lostFiles.join(', '));
        }
    }

    function restoreDraft(draft, fromKey) {
        try {
            applySnapshot(draft);
        } catch (e) {
            console.error('[learning draft] restore failed', e);
            notify('error', 'Sorry - the draft could not be restored.');
            arm();
            return;
        }
        if (fromKey) removeItem(fromKey);
        // Let the rebuilt editors/grids initialise, then record the restored state
        // as the current draft (submittedAt is null by construction).
        setTimeout(function() {
            arm();
            lastFingerprint = null;
            try {
                writeDraft(collectSnapshot(), true);
            } catch (e) {
                console.error('[learning draft] post-restore snapshot failed', e);
            }
            notify('success', 'Draft restored - remember to save the lesson.');
        }, SETTLE_MS);
    }

    // Signal A (edit only): a draft that was submitted, after which the server
    // content moved on, is a draft whose save landed.
    function isSuperseded(draft) {
        return !!(SERVER_VERSION && draft.submittedAt && draft.baseVersion && draft.baseVersion !== SERVER_VERSION);
    }

    function draftMessageLines(draft) {
        var lines = ['You have unsaved changes to this lesson from ' + formatWhen(draft.savedAt) + '.'];
        if (draft.submittedAt) {
            lines.push('A save was attempted at ' + formatWhen(draft.submittedAt) + ' but was never confirmed.');
        }
        if (SERVER_VERSION && draft.baseVersion && draft.baseVersion !== SERVER_VERSION) {
            lines.push('Warning: this lesson has been saved elsewhere since the draft was written. ' +
                'Restoring will replace those newer changes in the editor.');
        }
        return lines;
    }

    function stashForLater(draft) {
        try { trySetItem(PREVIOUS_KEY, JSON.stringify(draft)); } catch (e) { /* ignore */ }
        removeItem(KEY);
    }

    // --- "Decide later" banner (the stashed :previous copy) ------------------
    var $banner = null;
    function removeBanner() {
        if ($banner) { $banner.remove(); $banner = null; }
    }
    function showBanner(draft) {
        if (!draft) return;
        removeBanner();
        $banner = $(
            '<div class="alert alert-warning learning-draft-banner mb-3 d-flex flex-wrap align-items-center gap-2" role="alert">' +
                '<div class="flex-grow-1"><strong>Unsaved draft kept for later.</strong> ' +
                '<span class="learning-draft-banner-text"></span></div>' +
                '<button type="button" class="btn btn-sm btn-warning learning-draft-restore">Restore draft</button>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary learning-draft-discard">Discard draft</button>' +
                '<button type="button" class="btn-close learning-draft-dismiss" aria-label="Close"></button>' +
            '</div>'
        );
        $banner.find('.learning-draft-banner-text').text(draftMessageLines(draft).join(' '));
        $banner.find('.learning-draft-restore').on('click', function() {
            var stashed = readDraft(PREVIOUS_KEY);
            removeBanner();
            if (stashed) restoreDraft(stashed, PREVIOUS_KEY);
        });
        $banner.find('.learning-draft-discard').on('click', function() {
            removeItem(PREVIOUS_KEY);
            removeBanner();
        });
        $banner.find('.learning-draft-dismiss').on('click', removeBanner);
        $form.before($banner);
    }

    // --- Restore prompt ------------------------------------------------------
    function offerDraft(draft) {
        if (!window.Swal || typeof window.Swal.fire !== 'function') {
            // No modal available: keep the draft safe and fall back to the banner.
            stashForLater(draft);
            showBanner(readDraft(PREVIOUS_KEY));
            arm();
            return;
        }
        window.Swal.fire({
            title: 'Restore unsaved draft?',
            html: draftMessageLines(draft).map(escapeText).join('<br><br>'),
            icon: 'warning',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Restore draft',
            denyButtonText: 'Discard draft',
            cancelButtonText: 'Decide later',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(function(result) {
            if (result.isConfirmed) {
                restoreDraft(draft, null);
            } else if (result.isDenied) {
                removeItem(KEY);
                showBanner(readDraft(PREVIOUS_KEY));
                arm();
            } else {
                stashForLater(draft);
                showBanner(draft);
                arm();
            }
        });
    }

    // --- Submit hook (bound after the editor's own handlers) -----------------
    $form.on('submit', function() {
        try {
            inFlight = true;
            clearTimers();
            if (!armed) return;
            var snap = collectSnapshot();
            var fingerprint = fingerprintOf(snap);
            if (fingerprint === bootFingerprint) {
                // Nothing changed since load: nothing to protect, no pending marker.
                removeItem(KEY);
                lastFingerprint = fingerprint;
                return;
            }
            snap.submittedAt = Date.now();
            if (persist(snap, fingerprint)) trySetItem(PENDING_KEY, KEY);
        } catch (e) {
            console.error('[learning draft] submit hook failed', e);
        }
        // No preventDefault: the POST proceeds exactly as before.
    });

    // A page restored from the back/forward cache after a failed POST is no
    // longer mid-submit.
    $(window).on('pageshow', function(e) {
        if (e.originalEvent && e.originalEvent.persisted) inFlight = false;
    });

    // --- Unsaved-changes guard -----------------------------------------------
    $(window).on('beforeunload', function(e) {
        if (!armed || inFlight) return;
        var fingerprint;
        try {
            fingerprint = writeDraft(collectSnapshot(), false);
        } catch (err) {
            return;
        }
        if (fingerprint === bootFingerprint) return;
        e.preventDefault();
        if (e.originalEvent) e.originalEvent.returnValue = '';
        return '';
    });

    // --- Boot ----------------------------------------------------------------
    function init() {
        try {
            pruneExpired();
            bootFingerprint = fingerprintOf(collectSnapshot());

            var draft = readDraft(KEY);
            if (draft && (isSuperseded(draft) || fingerprintOf(draft) === bootFingerprint)) {
                removeItem(KEY);
                draft = null;
            }
            var previous = readDraft(PREVIOUS_KEY);
            if (previous && (isSuperseded(previous) || fingerprintOf(previous) === bootFingerprint)) {
                removeItem(PREVIOUS_KEY);
                previous = null;
            }

            if (draft) {
                offerDraft(draft);
            } else {
                if (previous) showBanner(previous);
                arm();
            }
        } catch (e) {
            console.error('[learning draft] init failed', e);
            arm();
        }
    }

    setTimeout(init, SETTLE_MS);
});
</script>
