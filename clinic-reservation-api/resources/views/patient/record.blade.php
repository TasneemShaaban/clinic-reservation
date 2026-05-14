<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Patient Record | ClinicReserve</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="booking-page patient-record-page" data-page="patient-record">
        <div class="booking-shell">
            <aside class="booking-sidebar" aria-label="Patient navigation">
                <div class="sidebar-brand">
                    <strong>Clinic Management</strong>
                    <span>Patient Portal</span>
                </div>

                <nav class="sidebar-nav">
                    <a href="#" class="sidebar-link">
                        <span class="sidebar-icon sidebar-figma-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 6V0H18V6H10ZM0 10V0H8V10H0ZM10 18V8H18V18H10ZM0 18V12H8V18H0ZM2 8H6V2H2V8ZM12 16H16V10H12V16ZM12 4H16V2H12V4ZM2 16H6V14H2V16Z" fill="#434655"/>
                            </svg>
                        </span>
                        Dashboard
                    </a>
                    <a href="{{ url('/patient/book-appointment') }}" class="sidebar-link">
                        <span class="sidebar-icon sidebar-figma-icon" aria-hidden="true">
                            <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 20C1.45 20 0.979167 19.8042 0.5875 19.4125C0.195833 19.0208 0 18.55 0 18V4C0 3.45 0.195833 2.97917 0.5875 2.5875C0.979167 2.19583 1.45 2 2 2H3V0H5V2H13V0H15V2H16C16.55 2 17.0208 2.19583 17.4125 2.5875C17.8042 2.97917 18 3.45 18 4V18C18 18.55 17.8042 19.0208 17.4125 19.4125C17.0208 19.8042 16.55 20 16 20H2ZM2 18H16V8H2V18ZM2 6H16V4H2V6Z" fill="#434655"/>
                            </svg>
                        </span>
                        Reservations
                    </a>
                    <a href="{{ url('/patient/book-appointment') }}" class="sidebar-link">
                        <span class="sidebar-icon sidebar-figma-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 20C1.45 20 0.979167 19.8042 0.5875 19.4125C0.195833 19.0208 0 18.55 0 18V6C0 5.45 0.195833 4.97917 0.5875 4.5875C0.979167 4.19583 1.45 4 2 4H6V2C6 1.45 6.19583 0.979167 6.5875 0.5875C6.97917 0.195833 7.45 0 8 0H12C12.55 0 13.0208 0.195833 13.4125 0.5875C13.8042 0.979167 14 1.45 14 2V4H18C18.55 4 19.0208 4.19583 19.4125 4.5875C19.8042 4.97917 20 5.45 20 6V18C20 18.55 19.8042 19.0208 19.4125 19.4125C19.0208 19.8042 18.55 20 18 20H2ZM8 4H12V2H8V4ZM9 13V16H11V13H14V11H11V8H9V11H6V13H9Z" fill="#434655"/>
                            </svg>
                        </span>
                        Doctors
                    </a>
                    <a href="#" class="sidebar-link">
                        <span class="sidebar-icon sidebar-figma-icon" aria-hidden="true">
                            <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 20C1.45 20 0.979167 19.8042 0.5875 19.4125C0.195833 19.0208 0 18.55 0 18V4C0 3.45 0.195833 2.97917 0.5875 2.5875C0.979167 2.19583 1.45 2 2 2H3V0H5V2H13V0H15V2H16C16.55 2 17.0208 2.19583 17.4125 2.5875C17.8042 2.97917 18 3.45 18 4V18C18 18.55 17.8042 19.0208 17.4125 19.4125C17.0208 19.8042 16.55 20 16 20H2ZM2 18H16V8H2V18ZM2 6H16V4H2V6Z" fill="#434655"/>
                            </svg>
                        </span>
                        Schedule
                    </a>
                    <a href="{{ url('/patient/record') }}" class="sidebar-link is-active">
                        <span class="sidebar-icon sidebar-figma-icon" aria-hidden="true">
                            <svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 13H17V12.45C17 11.7 16.6333 11.1042 15.9 10.6625C15.1667 10.2208 14.2 10 13 10C11.8 10 10.8333 10.2208 10.1 10.6625C9.36667 11.1042 9 11.7 9 12.45V13ZM13 9C13.55 9 14.0208 8.80417 14.4125 8.4125C14.8042 8.02083 15 7.55 15 7C15 6.45 14.8042 5.97917 14.4125 5.5875C14.0208 5.19583 13.55 5 13 5C12.45 5 11.9792 5.19583 11.5875 5.5875C11.1958 5.97917 11 6.45 11 7C11 7.55 11.1958 8.02083 11.5875 8.4125C11.9792 8.80417 12.45 9 13 9ZM2 16C1.45 16 0.979167 15.8042 0.5875 15.4125C0.195833 15.0208 0 14.55 0 14V2C0 1.45 0.195833 0.979167 0.5875 0.5875C0.979167 0.195833 1.45 0 2 0H8L10 2H18C18.55 2 19.0208 2.19583 19.4125 2.5875C19.8042 2.97917 20 3.45 20 4V14C20 14.55 19.8042 15.0208 19.4125 15.4125C19.0208 15.8042 18.55 16 18 16H2ZM2 14H18V4H9.175L7.175 2H2V14Z" fill="#006F66"/>
                            </svg>
                        </span>
                        Records
                    </a>
                </nav>

                <div class="sidebar-footer">
                    <a href="#" class="sidebar-link">Help Center</a>
                    <a href="#" class="sidebar-link">Logout</a>
                </div>
            </aside>

            <main class="booking-main">
                <header class="booking-topbar">
                    <div class="topbar-logo">ClinicReserve</div>
                    <label class="topbar-search">
                        <span class="search-dot"></span>
                        <input type="search" placeholder="Search records or appointments..." aria-label="Search records or appointments">
                        <kbd>Ctrl K</kbd>
                    </label>
                    <div class="topbar-actions" aria-label="Account actions">
                        <button class="topbar-icon-button topbar-figma-icon" type="button" aria-label="Notifications">
                            <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 17V15H2V8C2 6.61667 2.41667 5.3875 3.25 4.3125C4.08333 3.2375 5.16667 2.53333 6.5 2.2V1.5C6.5 1.08333 6.64583 0.729167 6.9375 0.4375C7.22917 0.145833 7.58333 0 8 0C8.41667 0 8.77083 0.145833 9.0625 0.4375C9.35417 0.729167 9.5 1.08333 9.5 1.5V2.2C10.8333 2.53333 11.9167 3.2375 12.75 4.3125C13.5833 5.3875 14 6.61667 14 8V15H16V17H0ZM8 20C7.45 20 6.97917 19.8042 6.5875 19.4125C6.19583 19.0208 6 18.55 6 18H10C10 18.55 9.80417 19.0208 9.4125 19.4125C9.02083 19.8042 8.55 20 8 20ZM4 15H12V8C12 6.9 11.6083 5.95833 10.825 5.175C10.0417 4.39167 9.1 4 8 4C6.9 4 5.95833 4.39167 5.175 5.175C4.39167 5.95833 4 6.9 4 8V15Z" fill="#434655"/>
                            </svg>
                        </button>
                        <button class="topbar-icon-button topbar-figma-icon" type="button" aria-label="Settings">
                            <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7.3 20L6.9 16.8C6.68333 16.7167 6.47917 16.6167 6.2875 16.5C6.09583 16.3833 5.90833 16.2583 5.725 16.125L2.75 17.375L0 12.625L2.575 10.675C2.55833 10.5583 2.55 10.4458 2.55 10.3375C2.55 10.2292 2.55 10.1167 2.55 10C2.55 9.88333 2.55 9.77083 2.55 9.6625C2.55 9.55417 2.55833 9.44167 2.575 9.325L0 7.375L2.75 2.625L5.725 3.875C5.90833 3.74167 6.1 3.61667 6.3 3.5C6.5 3.38333 6.7 3.28333 6.9 3.2L7.3 0H12.8L13.2 3.2C13.4167 3.28333 13.6208 3.38333 13.8125 3.5C14.0042 3.61667 14.1917 3.74167 14.375 3.875L17.35 2.625L20.1 7.375L17.525 9.325C17.5417 9.44167 17.55 9.55417 17.55 9.6625C17.55 9.77083 17.55 9.88333 17.55 10C17.55 10.1167 17.55 10.2292 17.55 10.3375C17.55 10.4458 17.5333 10.5583 17.5 10.675L20.075 12.625L17.325 17.375L14.375 16.125C14.1917 16.2583 14 16.3833 13.8 16.5C13.6 16.6167 13.4 16.7167 13.2 16.8L12.8 20H7.3Z" fill="#434655"/>
                            </svg>
                        </button>
                        <span class="topbar-divider"></span>
                        <a href="#">Logout</a>
                        <span id="record-avatar-top" class="avatar-placeholder">P</span>
                    </div>
                </header>

                <section class="record-content">
                    <div class="record-heading">
                        <div>
                            <p>Records / <span>Patient Profile</span></p>
                            <h1>Patient Record</h1>
                            <strong>Comprehensive overview and session history.</strong>
                        </div>
                        <div class="record-heading-actions" aria-label="Record actions">
                            <button class="record-outline-button" type="button">Edit Profile</button>
                            <button class="record-primary-button" type="button">Print Record</button>
                        </div>
                    </div>

                    <div id="record-feedback" class="booking-feedback is-hidden" aria-live="polite"></div>

                    <div class="record-grid">
                        <section class="record-card patient-summary-card" aria-labelledby="patient-summary-title">
                            <div id="patient-avatar" class="patient-avatar">P</div>
                            <div class="patient-summary-main">
                                <div class="patient-summary-header">
                                    <div>
                                        <h2 id="patient-summary-title">Loading patient...</h2>
                                        <p id="patient-mrn">MRN loading</p>
                                    </div>
                                    <span id="patient-status" class="record-status-badge is-active">Active Patient</span>
                                </div>
                                <div class="patient-facts">
                                    <div>
                                        <span>Age / DOB</span>
                                        <strong id="patient-age">Loading</strong>
                                        <p id="patient-dob">Loading</p>
                                    </div>
                                    <div>
                                        <span>Contact</span>
                                        <strong id="patient-email">Loading</strong>
                                        <p id="patient-phone">Loading</p>
                                    </div>
                                    <div>
                                        <span>Primary Care Physician</span>
                                        <strong id="patient-physician">Loading</strong>
                                        <p id="patient-address">Loading</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <aside class="record-card clinical-card" aria-labelledby="clinical-title">
                            <h2 id="clinical-title">Clinical Indicators</h2>
                            <div class="clinical-list">
                                <div>
                                    <span>Blood Type</span>
                                    <strong id="clinical-blood-type">Loading</strong>
                                </div>
                                <div>
                                    <span>Allergies</span>
                                    <strong id="clinical-allergies">Loading</strong>
                                </div>
                                <div>
                                    <span>Last Visit</span>
                                    <strong id="clinical-last-visit">Loading</strong>
                                </div>
                                <div>
                                    <span>Medical History</span>
                                    <strong id="clinical-medical-history">Loading</strong>
                                </div>
                                <div>
                                    <span>Prescription Info</span>
                                    <strong id="clinical-prescriptions">Loading</strong>
                                </div>
                            </div>
                        </aside>
                    </div>

                    <section class="record-card appointments-card" aria-labelledby="appointments-title">
                        <div class="record-card-header">
                            <div>
                                <h2 id="appointments-title">My Appointments</h2>
                                <p>Session details, reservation codes, and appointment status.</p>
                            </div>
                            <button class="record-filter-button" type="button" aria-label="Filter appointments">
                                <svg width="15" height="15" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M8.889 16.667H11.111V14.444H8.889V16.667ZM1.111 3.333V5.556H18.889V3.333H1.111ZM4.444 11.111H15.556V8.889H4.444V11.111Z" fill="currentColor"/>
                                </svg>
                            </button>
                        </div>

                        <div class="appointments-table-wrap">
                            <table class="appointments-table">
                                <thead>
                                    <tr>
                                        <th>Doctor</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="appointments-table-body">
                                    <tr>
                                        <td colspan="5">Loading appointments...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="appointments-empty" class="record-empty-state is-hidden">
                            No appointments found yet.
                        </div>
                        <div class="appointments-footer">
                            <span id="appointments-footer-count">Showing 0 records</span>
                            <div class="appointments-pagination" aria-label="Appointments pagination">
                                <button type="button" aria-label="Previous appointments">&lt;</button>
                                <button type="button" aria-label="Next appointments">&gt;</button>
                            </div>
                        </div>
                    </section>
                </section>
            </main>
        </div>

        <div id="cancel-modal" class="record-modal is-hidden" role="dialog" aria-modal="true" aria-labelledby="cancel-modal-title">
            <div class="record-modal-backdrop" data-close-cancel-modal></div>
            <div class="record-modal-panel">
                <div class="record-modal-icon" aria-hidden="true">!</div>
                <h2 id="cancel-modal-title">Cancel Appointment</h2>
                <p id="cancel-modal-summary">Select an appointment to cancel.</p>
                <strong>This action cannot be undone.</strong>
                <div class="record-modal-actions">
                    <button id="keep-appointment" class="record-secondary-button" type="button">Keep Appointment</button>
                    <button id="confirm-cancel-appointment" class="record-danger-button" type="button">Cancel Appointment</button>
                </div>
            </div>
        </div>
    </body>
</html>
