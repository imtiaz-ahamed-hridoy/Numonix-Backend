@php
    $systemSetting = App\Models\SystemSetting::first();
    $logo =
        $systemSetting && $systemSetting->logo
        ? url($systemSetting->logo)
        : null;
@endphp

<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar">
        <div class="side-header">
            <a class="header-brand1" href="{{ route('dashboard') }}">
                <img src="{{ $logo ?? asset('frontend/logo.png') }}" class="header-brand-img desktop-logo" alt="logo">
                <img src="{{ $logo ?? asset('frontend/logo.png') }}" class="header-brand-img toggle-logo" alt="logo">
                <img src="{{ $logo ?? asset('frontend/logo.png') }}" class="header-brand-img light-logo" alt="logo">
                <img src="{{ $logo ?? asset('frontend/logo.png') }}" class="header-brand-img light-logo1" alt="logo">
            </a>
        </div>

        <div class="main-sidemenu">
            <div class="slide-left disabled" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                </svg>
            </div>

            <ul class="side-menu">
                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('dashboard') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon"
                            enable-background="new 0 0 24 24" viewBox="0 0 24 24">
                            <path
                                d="M19.9794922,7.9521484l-6-5.2666016c-1.1339111-0.9902344-2.8250732-0.9902344-3.9589844,0l-6,5.2666016C3.3717041,8.5219116,2.9998169,9.3435669,3,10.2069702V19c0.0018311,1.6561279,1.3438721,2.9981689,3,3h2.5h7c0.0001831,0,0.0003662,0,0.0006104,0H18c1.6561279-0.0018311,2.9981689-1.3438721,3-3v-8.7930298C21.0001831,9.3435669,20.6282959,8.5219116,19.9794922,7.9521484z M15,21H9v-6c0.0014038-1.1040039,0.8959961-1.9985962,2-2h2c1.1040039,0.0014038,1.9985962,0.8959961,2,2V21z M20,19c-0.0014038,1.1040039-0.8959961,1.9985962-2,2h-2v-6c-0.0018311-1.6561279-1.3438721-2.9981689-3-3h-2c-1.6561279,0.0018311-2.9981689,1.3438721-3,3v6H6c-1.1040039-0.0014038-1.9985962-0.8959961-2-2v-8.7930298C3.9997559,9.6313477,4.2478027,9.0836182,4.6806641,8.7041016l6-5.2666016C11.0455933,3.1174927,11.5146484,2.9414673,12,2.9423828c0.4853516-0.0009155,0.9544067,0.1751099,1.3193359,0.4951172l6,5.2665405C19.7521973,9.0835571,20.0002441,9.6313477,20,10.2069702V19z" />
                        </svg>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('user.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>

                        <span class="side-menu__label"> Users</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item has-link {{ request()->routeIs('founders.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('founders.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>

                        <span class="side-menu__label"> Founders</span>
                    </a>
                </li>

                <li class="slide {{ request()->routeIs('guest-access.*') || request()->routeIs('guest-analytics.*') ? 'active is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('guest-access.*') || request()->routeIs('guest-analytics.*') ? 'active is-expanded' : '' }}" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="side-menu__label"> User Management</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('guest-access.index') }}" class="slide-item {{ request()->routeIs('guest-access.*') ? 'active' : '' }}">Access Control</a></li>
                        {{-- <li><a href="{{ route('guest-analytics.index') }}" class="slide-item {{ request()->routeIs('guest-analytics.*') ? 'active' : '' }}">User Analytics</a></li> --}}
                    </ul>
                </li>

                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('review.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" class="side-menu__icon"
                            viewBox="0 0 640 640">
                            <path
                                d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z" />
                        </svg>
                        <span class="side-menu__label"> Reviews</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('faq.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 640 640">
                            <path
                                d="M280 88C280 57.1 254.9 32 224 32C193.1 32 168 57.1 168 88C168 118.9 193.1 144 224 144C254.9 144 280 118.9 280 88zM304 300.7L341 350.6C353.8 333.1 369.5 317.9 387.3 305.6L331.1 229.9C306 196 266.3 176 224 176C181.7 176 142 196 116.8 229.9L46.3 324.9C35.8 339.1 38.7 359.1 52.9 369.7C67.1 380.3 87.1 377.3 97.7 363.1L144 300.7L144 576C144 593.7 158.3 608 176 608C193.7 608 208 593.7 208 576L208 416C208 407.2 215.2 400 224 400C232.8 400 240 407.2 240 416L240 576C240 593.7 254.3 608 272 608C289.7 608 304 593.7 304 576L304 300.7zM496 608C575.5 608 640 543.5 640 464C640 384.5 575.5 320 496 320C416.5 320 352 384.5 352 464C352 543.5 416.5 608 496 608zM496 508C507 508 516 517 516 528C516 539 507 548 496 548C485 548 476 539 476 528C476 517 485 508 496 508zM496 408C484.4 408 474.7 416.2 472.5 427.2C470.7 435.9 462.3 441.5 453.6 439.7C444.9 437.9 439.3 429.5 441.1 420.8C446.3 395.2 468.9 376 496 376C526.9 376 552 401.1 552 432C552 451.8 540.3 469.8 522.2 477.9L511.8 482.5C510.6 490.2 504 496 496 496C487.2 496 480 488.8 480 480C480 468.8 486.6 458.7 496.8 454.1L509.2 448.6C515.8 445.7 520 439.2 520 432C520 418.7 509.3 408 496 408z" />
                        </svg>
                        <span class="side-menu__label"> Faqs</span>
                    </a>
                </li>

                <li class="slide {{ request()->routeIs('cms.*') ? 'active is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('cms.*') ? 'active is-expanded' : '' }}" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 4h18v14H3z" />
                            <path d="M3 8h18" />
                        </svg>
                        <span class="side-menu__label"> CMS Pages </span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('cms.user-guide.index') }}" class="slide-item {{ request()->routeIs('cms.user-guide.*') ? 'active' : '' }}">User Guide</a></li>
                        <li><a href="{{ route('cms.terms-conditions.index') }}" class="slide-item {{ request()->routeIs('cms.terms-conditions.*') ? 'active' : '' }}">Terms & Conditions</a></li>
                        <li><a href="{{ route('cms.privacy-policy.index') }}" class="slide-item {{ request()->routeIs('cms.privacy-policy.*') ? 'active' : '' }}">Privacy Policy</a></li>
                        <li><a href="{{ route('cms.glossary.index') }}" class="slide-item {{ request()->routeIs('cms.glossary.*') ? 'active' : '' }}">Glossary</a></li>
                        <li><a href="{{ route('cms.complete-glossary.index') }}" class="slide-item {{ request()->routeIs('cms.complete-glossary.*') ? 'active' : '' }}">Complete Glossary</a></li>
                        <li><a href="{{ route('cms.about-numonix.index') }}" class="slide-item {{ request()->routeIs('cms.about-numonix.*') ? 'active' : '' }}">About NumoniX</a></li>
                    </ul>
                </li>

                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide"
                        href="{{ route('custom-notifications.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span class="side-menu__label"> Custom Notifications </span>
                    </a>
                </li>

                <li class="slide {{ request()->routeIs('activity.*') ? 'active is-expanded' : '' }}">
                    <a class="side-menu__item has-link {{ request()->routeIs('activity.*') ? 'active' : '' }}" data-bs-toggle="slide"
                        href="{{ route('activity.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <span class="side-menu__label"> Activity Logs </span>
                    </a>
                </li>

                <li class="slide {{ request()->routeIs('home-module.*') ? 'active' : '' }}">
                    <a class="side-menu__item has-link {{ request()->routeIs('home-module.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('home-module.index') }}">
                        <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span class="side-menu__label"> Home Modules </span>
                    </a>
                </li>

                <li class="slide {{ request()->routeIs('learning.*') ? 'active' : '' }}">
                    <a class="side-menu__item has-link {{ request()->routeIs('learning.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('learning.index') }}">
                        <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                            <path
                                d="M192 144C222.9 144 248 118.9 248 88C248 57.1 222.9 32 192 32C161.1 32 136 57.1 136 88C136 118.9 161.1 144 192 144zM176 576L176 416C176 407.2 183.2 400 192 400C200.8 400 208 407.2 208 416L208 576C208 593.7 222.3 608 240 608C257.7 608 272 593.7 272 576L272 240L400 240C417.7 240 432 225.7 432 208C432 190.3 417.7 176 400 176L384 176L384 128L576 128L576 320L384 320L384 288L320 288L320 336C320 362.5 341.5 384 368 384L592 384C618.5 384 640 362.5 640 336L640 112C640 85.5 618.5 64 592 64L368 64C341.5 64 320 85.5 320 112L320 176L197.3 176C151.7 176 108.8 197.6 81.7 234.2L14.3 324.9C3.8 339.1 6.7 359.1 20.9 369.7C35.1 380.3 55.1 377.3 65.7 363.1L112 300.7L112 576C112 593.7 126.3 608 144 608C161.7 608 176 593.7 176 576z" />
                        </svg>
                        <span class="side-menu__label"> Learning CMS </span>
                    </a>
                </li>



                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('quiz.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 32 32" fill="none">
                            <g id="SVGRepo_iconCarrier">
                                <path
                                    d="M23 2.01001H9C7.34315 2.01001 6 3.35316 6 5.01001V27.01C6 28.6669 7.34315 30.01 9 30.01H23C24.6569 30.01 26 28.6669 26 27.01V5.01001C26 3.35316 24.6569 2.01001 23 2.01001Z"
                                    fill="#CFD8DC"></path>
                                <path
                                    d="M21 25.9999C20.8684 26.0007 20.7379 25.9755 20.6161 25.9257C20.4943 25.876 20.3834 25.8026 20.29 25.7099L17.29 22.7099C17.1037 22.5226 16.9992 22.2691 16.9992 22.0049C16.9992 21.7408 17.1037 21.4873 17.29 21.2999C17.383 21.2062 17.4936 21.1318 17.6154 21.0811C17.7373 21.0303 17.868 21.0042 18 21.0042C18.132 21.0042 18.2627 21.0303 18.3846 21.0811C18.5064 21.1318 18.617 21.2062 18.71 21.2999L21.71 24.2999C21.8037 24.3929 21.8781 24.5035 21.9289 24.6254C21.9797 24.7472 22.0058 24.8779 22.0058 25.0099C22.0058 25.142 21.9797 25.2727 21.9289 25.3945C21.8781 25.5164 21.8037 25.627 21.71 25.7199C21.5197 25.9035 21.2644 26.0042 21 25.9999Z"
                                    fill="#E53935"></path>
                                <path
                                    d="M18 25.9999C17.8684 26.0007 17.7379 25.9755 17.6161 25.9257C17.4943 25.8759 17.3834 25.8026 17.29 25.7099C17.1963 25.617 17.1219 25.5064 17.0711 25.3845C17.0203 25.2627 16.9942 25.132 16.9942 24.9999C16.9942 24.8679 17.0203 24.7372 17.0711 24.6154C17.1219 24.4935 17.1963 24.3829 17.29 24.2899L20.29 21.2899C20.383 21.1962 20.4936 21.1218 20.6154 21.071C20.7373 21.0203 20.868 20.9941 21 20.9941C21.132 20.9941 21.2627 21.0203 21.3846 21.071C21.5064 21.1218 21.617 21.1962 21.71 21.2899C21.8963 21.4773 22.0008 21.7308 22.0008 21.9949C22.0008 22.2591 21.8963 22.5126 21.71 22.6999L18.71 25.6999C18.6174 25.7944 18.5069 25.8696 18.3851 25.9211C18.2632 25.9726 18.1323 25.9994 18 25.9999Z"
                                    fill="#E53935"></path>
                                <path
                                    d="M12 26H11.94C11.7629 25.9896 11.5918 25.9322 11.4442 25.8338C11.2965 25.7354 11.1778 25.5995 11.1 25.44L10.1 23.44C9.99153 23.2039 9.97925 22.9348 10.0658 22.6897C10.1523 22.4447 10.3308 22.2429 10.5635 22.1272C10.7962 22.0115 11.0648 21.991 11.3124 22.0699C11.56 22.1488 11.7672 22.321 11.89 22.55L12.12 23.01L13.17 21.44C13.3237 21.2358 13.5497 21.0981 13.8017 21.0553C14.0537 21.0125 14.3125 21.0678 14.525 21.2099C14.7375 21.3519 14.8875 21.57 14.9443 21.8193C15.001 22.0685 14.9601 22.33 14.83 22.55L12.83 25.55C12.7394 25.6876 12.6163 25.8007 12.4715 25.8792C12.3267 25.9577 12.1647 25.9992 12 26Z"
                                    fill="#689F38"></path>
                                <path d="M26 17.01H6V19.01H26V17.01Z" fill="#495584"></path>
                                <path
                                    d="M14 11H11C10.7348 11 10.4804 10.8946 10.2929 10.7071C10.1054 10.5196 10 10.2652 10 10V7C10 6.73478 10.1054 6.48043 10.2929 6.29289C10.4804 6.10536 10.7348 6 11 6H14C14.2652 6 14.5196 6.10536 14.7071 6.29289C14.8946 6.48043 15 6.73478 15 7V10C15 10.2652 14.8946 10.5196 14.7071 10.7071C14.5196 10.8946 14.2652 11 14 11ZM12 9H13V8H12V9Z"
                                    fill="#495584"></path>
                                <path
                                    d="M21 15H11C10.7348 15 10.4804 14.8946 10.2929 14.7071C10.1054 14.5196 10 14.2652 10 14C10 13.7348 10.1054 13.4804 10.2929 13.2929C10.4804 13.1054 10.7348 13 11 13H21C21.2652 13 21.5196 13.1054 21.7071 13.2929C21.8946 13.4804 22 13.7348 22 14C22 14.2652 21.8946 14.5196 21.7071 14.7071C21.5196 14.8946 21.2652 15 21 15Z"
                                    fill="#495584"></path>
                                <path
                                    d="M21 8H18C17.7348 8 17.4804 7.89464 17.2929 7.70711C17.1054 7.51957 17 7.26522 17 7C17 6.73478 17.1054 6.48043 17.2929 6.29289C17.4804 6.10536 17.7348 6 18 6H21C21.2652 6 21.5196 6.10536 21.7071 6.29289C21.8946 6.48043 22 6.73478 22 7C22 7.26522 21.8946 7.51957 21.7071 7.70711C21.5196 7.89464 21.2652 8 21 8Z"
                                    fill="#495584"></path>
                                <path
                                    d="M21 11H18C17.7348 11 17.4804 10.8946 17.2929 10.7071C17.1054 10.5196 17 10.2652 17 10C17 9.73478 17.1054 9.48043 17.2929 9.29289C17.4804 9.10536 17.7348 9 18 9H21C21.2652 9 21.5196 9.10536 21.7071 9.29289C21.8946 9.48043 22 9.73478 22 10C22 10.2652 21.8946 10.5196 21.7071 10.7071C21.5196 10.8946 21.2652 11 21 11Z"
                                    fill="#495584"></path>
                                <path
                                    d="M9 2C8.20435 2 7.44129 2.31607 6.87868 2.87868C6.31607 3.44129 6 4.20435 6 5V27C6 27.7956 6.31607 28.5587 6.87868 29.1213C7.44129 29.6839 8.20435 30 9 30H16V2H9Z"
                                    fill="#EEEEEE"></path>
                                <path
                                    d="M13.17 21.45L12.12 23L11.89 22.54C11.7672 22.311 11.56 22.1388 11.3124 22.0599C11.0648 21.9809 10.7962 22.0015 10.5635 22.1172C10.3308 22.2329 10.1523 22.4347 10.0658 22.6797C9.97925 22.9248 9.99153 23.1939 10.1 23.43L11.1 25.43C11.1778 25.5895 11.2965 25.7254 11.4442 25.8238C11.5918 25.9222 11.7629 25.9796 11.94 25.99H12C12.1647 25.9892 12.3267 25.9477 12.4715 25.8692C12.6163 25.7907 12.7394 25.6776 12.83 25.54L14.83 22.54C14.9601 22.32 15.001 22.0585 14.9443 21.8092C14.8875 21.56 14.7375 21.3419 14.525 21.1999C14.3125 21.0578 14.0537 21.0024 13.8017 21.0453C13.5497 21.0881 13.3237 21.2258 13.17 21.43V21.45Z"
                                    fill="#689F38"></path>
                                <path d="M16 17.01H6V19.01H16V17.01Z" fill="#495584"></path>
                                <path
                                    d="M14 11H11C10.7348 11 10.4804 10.8946 10.2929 10.7071C10.1054 10.5196 10 10.2652 10 10V7C10 6.73478 10.1054 6.48043 10.2929 6.29289C10.4804 6.10536 10.7348 6 11 6H14C14.2652 6 14.5196 6.10536 14.7071 6.29289C14.8946 6.48043 15 6.73478 15 7V10C15 10.2652 14.8946 10.5196 14.7071 10.7071C14.5196 10.8946 14.2652 11 14 11ZM12 9H13V8H12V9Z"
                                    fill="#495584"></path>
                                <path
                                    d="M11 13C10.7348 13 10.4804 13.1054 10.2929 13.2929C10.1054 13.4804 10 13.7348 10 14C10 14.2652 10.1054 14.5196 10.2929 14.7071C10.4804 14.8946 10.7348 15 11 15H16V13H11Z"
                                    fill="#495584"></path>
                                <path
                                    d="M23 2H9C8.20435 2 7.44129 2.31607 6.87868 2.87868C6.31607 3.44129 6 4.20435 6 5V27C6 27.7956 6.31607 28.5587 6.87868 29.1213C7.44129 29.6839 8.20435 30 9 30H23C23.7956 30 24.5587 29.6839 25.1213 29.1213C25.6839 28.5587 26 27.7956 26 27V5C26 4.20435 25.6839 3.44129 25.1213 2.87868C24.5587 2.31607 23.7956 2 23 2ZM24 27C24 27.2652 23.8946 27.5196 23.7071 27.7071C23.5196 27.8946 23.2652 28 23 28H9C8.73478 28 8.48043 27.8946 8.29289 27.7071C8.10536 27.5196 8 27.2652 8 27V5C8 4.73478 8.10536 4.48043 8.29289 4.29289C8.48043 4.10536 8.73478 4 9 4H23C23.2652 4 23.5196 4.10536 23.7071 4.29289C23.8946 4.48043 24 4.73478 24 5V27Z"
                                    fill="#495584"></path>
                            </g>
                        </svg>
                        <span class="side-menu__label"> Quiz Page </span>
                    </a>
                </li>

                <li class="slide {{ request()->routeIs('ear-training.*') || request()->routeIs('admin.ear-training.*') ? 'active is-expanded' : '' }}">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);">
                        <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 18V5l12-2v13"></path>
                            <circle cx="6" cy="18" r="3"></circle>
                            <circle cx="18" cy="16" r="3"></circle>
                        </svg>
                        <span class="side-menu__label"> Ear Training </span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.ear-training.stages.index') }}" class="slide-item {{ request()->routeIs('admin.ear-training.stages.*', 'admin.ear-training.levels.*') ? 'active' : '' }}">Stages & Levels</a></li>
                        <li><a href="{{ route('admin.ear-training.templates.index') }}" class="slide-item {{ request()->routeIs('admin.ear-training.templates.*') ? 'active' : '' }}">Explanation Templates</a></li>
                    </ul>
                </li>


                <hr>

                <li class="slide {{ request()->is('admin/settings*') ? 'active is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->is('admin/settings*') ? 'active is-expanded' : '' }}"
                        data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 512 512">
                            <path
                                d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" />
                        </svg>
                        <span class="side-menu__label">Settings</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('profile.setting') }}" class="slide-item">Profile Settings</a></li>
                        @if (Auth::user()->role === 'Admin')
                            <li><a href="{{ route('system.index') }}" class="slide-item">System Settings</a></li>
                            <li><a href="{{ route('mail.setting') }}" class="slide-item">Mail Settings</a></li>
                            {{-- Stripe removed - Paddle is the active payment provider --}}
                            <li><a href="{{ route('firebase.setting') }}" class="slide-item">Firebase Settings</a></li>
                            <li><a href="{{ route('update-alerts.index') }}" class="slide-item">Update Alerts</a></li>
                        @endif
                    </ul>
                </li>

                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide" href="javascript:void(0);"
                        onclick="confirmLogout()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 640 640">
                            <path
                                d="M569 337C578.4 327.6 578.4 312.4 569 303.1L425 159C418.1 152.1 407.8 150.1 398.8 153.8C389.8 157.5 384 166.3 384 176L384 256L272 256C245.5 256 224 277.5 224 304L224 336C224 362.5 245.5 384 272 384L384 384L384 464C384 473.7 389.8 482.5 398.8 486.2C407.8 489.9 418.1 487.9 425 481L569 337zM224 160C241.7 160 256 145.7 256 128C256 110.3 241.7 96 224 96L160 96C107 96 64 139 64 192L64 448C64 501 107 544 160 544L224 544C241.7 544 256 529.7 256 512C256 494.3 241.7 480 224 480L160 480C142.3 480 128 465.7 128 448L128 192C128 174.3 142.3 160 160 160L224 160z" />
                        </svg>

                        <span class="side-menu__label"> Logout</span>
                    </a>
                </li>
            </ul>

            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                </svg>
            </div>
        </div>
    </div>
</div>
