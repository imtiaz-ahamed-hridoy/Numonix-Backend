<?php

namespace App\Traits;

use App\Helpers\Helper;
use Illuminate\Http\JsonResponse;
use Livewire\Attributes\On;

/**
 * Provides Livewire-powered delete confirmation and status toggle actions
 * for admin data tables.
 *
 * Expects the using Livewire component to define $model, $deleteFilesColumn,
 * and $statusChangeValue properties.
 */
Trait TableAction
{

    /**
     * Dispatch a Livewire event to confirm deletion of a record.
     *
     * @param  mixed  $id  The record ID to delete
     */
    public function deleteConfirm($id): void
    {
       $this->dispatch("deleteConfirm",$id);
   }

    /**
     * Delete a record by ID, including associated files, with flash feedback.
     *
     * Responds to the Livewire 'delete' event. Expects $this->model to be
     * an Eloquent model class string and $this->deleteFilesColumn to be
     * an array of column names containing file paths.
     *
     * @param  mixed  $id  The record ID to delete
     */
    #[On('delete')]
    public function delete($id): void
    {
        try {
            $record = $this->model::find($id);
            $files = $this->deleteFilesColumn ?? [];
            foreach ($files as $f) {
                if ($record->{$f} && file_exists(public_path($record->{$f}))) {
                    Helper::fileDelete(public_path($record->{$f}));
                }
            }
            if ($record) {
                $record->delete();
                flash()->success("Record deleted successfully");
            }else{
                flash()->error("Record not found");
            }
        }catch (\Throwable $exception){
            flash()->error($exception->getMessage());
        }
    }

    /**
     * Toggle the status of a record between predefined values.
     *
     * Expects $this->statusChangeValue to be an associative array
     * mapping status labels to boolean values.
     *
     * @param  mixed  $id  The record ID to toggle
     */
    public function statusChange($id): void
    {
        try {
            $record = $this->model::find($id);
            if (isset($this->statusChangeValue)) {
                $record->update([
                    'status' => array_search(!$this->statusChangeValue[$record->status], $this->statusChangeValue)
                ]);
                flash()->success("Record status changed successfully");
            }else{
                flash()->error("Set public property statusChange with boolean value and must be array");
            }
        }catch (\Throwable $exception){
            flash()->error($exception->getMessage());
        }
    }
}
