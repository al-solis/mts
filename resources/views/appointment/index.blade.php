@extends('dashboard')
@section('content')
    @php
        use Illuminate\Support\Str;
        use Carbon\Carbon;
    @endphp

    <div class="p-6 space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Appointments</h1>
                <p class="text-sm text-gray-500">
                    Manage interviews and meetings with candidates.
                </p>
            </div>
            <div class="flex items-center gap-2 mt-0">
                {{-- <a href="{{ route('setup.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-medium text-gray border border-gray-300 bg-gray-100 rounded-lg hover:bg-gray-200 ">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </a> --}}

                <button data-modal-target="add-modal" data-modal-toggle="add-modal"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Schedule Appointment
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        @php
            $cards = [
                [
                    'title' => 'Total Appointments',
                    'value' => $totalAppointments,
                    'color' => 'gray',
                    'icon' => '
                        <svg xmlns="http://www.w3.org/2000/svg" class = "w-5 h-5 text-gray-600" width="16" height="16" fill="currentColor" class="bi bi-calendar-week" viewBox="0 0 16 16">
                        <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>
                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                        </svg>',
                ],
                [
                    'title' => 'Upcoming',
                    'value' => $upcomingAppointments,
                    'color' => 'blue',
                    'icon' => '
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" width="16" height="16"
                        class="bi bi-calendar2" viewBox="0 0 16 16">                            
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                        <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5z"/>
                        </svg>',
                ],
                [
                    'title' => 'Completed',
                    'value' => $completedAppointments,
                    'color' => 'green',
                    'icon' => '
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" width="16" height="16"
                        class="bi bi-calendar-check" viewBox="0 0 16 16">
                            <path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                            </svg>',
                ],
                [
                    'title' => 'Cancelled',
                    'value' => $cancelledAppointments,
                    'color' => 'red',
                    'icon' => '
                        <svg class="w-5 h-5 text-red-600" width="16" height="16" fill="currentColor" class="bi bi-calendar-x" viewBox="0 0 16 16">
                            <path d="M6.146 7.146a.5.5 0 0 1 .708 0L8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 0 1 0-.708"/>
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                            </svg>',
                ],
            ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($cards as $card)
                <div class="bg-white border rounded-xl p-4 flex items-center gap-4">
                    <div
                        class="w-10 h-10 rounded-lg bg-{{ $card['color'] }}-100 
                                flex items-center justify-center">
                        {!! $card['icon'] !!}
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">{{ $card['title'] }}</p>
                        <p class="text-xl font-semibold text-gray-900">
                            {{ $card['value'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 pt-1">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @elseif (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 pt-1"
                data-success="true">
                {{ session('success') }}
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Clear form fields after successful submission
                    clearModalFields();
                });
            </script>
        @endif

        {{-- Filters --}}
        <form action="" method="GET">
            <div class="flex flex-col md:flex-row gap-2 text-xs md:text-sm">
                <div class="md:w-2/3 w-full">
                    <input type="text" id="simple-search" name="search" placeholder="Search by name or description..."
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                        value = "{{ request()->query('search') }}" oninput="this.form.submit()">
                </div>

                <div class="md:w-2/3 w-full">
                    <select id="searchloc" name="searchloc"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                        onchange="this.form.submit()">
                        <option value="" selected>All Companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ request('searchloc') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:w-1/3 w-full">
                    <select id="status" name="status"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                        onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Completed</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Scheduled</option>
                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </div>
            <button type="submit"
                class="hidden mt-4 w-full shrink-0 rounded-lg bg-gray-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 sm:mt-0 sm:w-auto">Search</button>
        </form>

        <div class="bg-white rounded-xl border p-4">
            <h2 class="text-lg font-semibold mb-4">Upcoming Appointments</h2>

            @forelse ($upcoming as $appt)
                <div class="flex items-center justify-between border rounded-xl p-4 mb-3">
                    <div class="flex gap-4">
                        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z" />
                            </svg>
                        </div>

                        <div>
                            <p class="font-semibold text-gray-900">
                                {{ $appt->resume->applicant_name }}
                                <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700">
                                    {{ $appt->meeting_type == 2 ? 'Online' : 'In-person' }}
                                </span>
                                <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-gray-100">
                                    @php
                                        $roundLable = [
                                            1 => 'First Round',
                                            2 => '2nd Round',
                                            3 => 'Third Round',
                                            4 => 'Final Round',
                                            5 => 'Other',
                                        ];
                                    @endphp
                                    {{ $roundLable[$appt->interview_round] ?? 'Other' }}
                                </span>

                                @if ($appt->status == 0 && $appt->interview_date->isToday())
                                    <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-700">
                                        Today
                                    </span>
                                @elseif ($appt->status == 0 && $appt->interview_date->isPast())
                                    <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700">
                                        Overdue
                                    </span>
                                @endif
                            </p>

                            <p class="text-sm text-gray-500">
                                {{ $appt->resume->job->title ?? '—' }}
                            </p>

                            <div class="flex gap-4 text-xs text-gray-500 mt-1">
                                <span><i class="bi bi-calendar"></i> {{ $appt->interview_date->format('m/d/Y') }}</span>
                                <span><i class="bi bi-clock"></i>
                                    {{ Carbon::parse($appt->interview_time)->format('H:i') }}</span>
                            </div>

                            <p class="italic text-sm text-gray-500 mt-1">
                                {{ $appt->notes }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" data-modal-target="edit-modal" data-modal-toggle="edit-modal"
                            data-id = "{{ $appt->id }}" data-resume-id = "{{ $appt->resume_id }}"
                            data-company-name = "{{ $appt->resume->job->company->name ?? '' }}"
                            data-job-title = "{{ $appt->resume->job->title ?? '' }}"
                            data-applicant-name = "{{ $appt->resume->applicant_name }}"
                            data-interview-date = "{{ $appt->interview_date->format('Y-m-d') }}"
                            data-interview-time = "{{ Carbon::parse($appt->interview_time)->format('H:i') }}"
                            data-interview-round = "{{ $appt->interview_round }}"
                            data-meeting-type = "{{ $appt->meeting_type }}"
                            data-meeting-link = "{{ $appt->meeting_link }}" data-notes = "{{ $appt->notes }}"
                            data-status = "{{ $appt->status }}" onclick="editAppointment(this)"
                            class="px-3 py-1 text-xs border rounded-lg hover:bg-gray-100">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>

                        <button type="button" onclick="markAsComplete(this)" data-id = "{{ $appt->id }}"
                            data-applicant-name = "{{ $appt->resume->applicant_name }}"
                            class="px-3 py-1 text-white inline-flex items-center bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-md text-xs text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                            <i class="bi bi-check-circle"></i><span class="ml-1">Complete</span>
                        </button>

                        <button type="button" data-id = "{{ $appt->id }}"
                            data-applicant-name="{{ $appt->resume->applicant_name }}" onclick="markAsCancelled(this)"
                            class="px-3 py-1 text-xs text-red-600 border border-red-300 rounded-lg">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>

                    </div>
                </div>
            @empty
                <p class="text-center text-gray-400 py-6">No upcoming appointments</p>
            @endforelse
        </div>

        @if ($completedAppointments !== 0)
            <div class="bg-white rounded-xl border p-4 mt-6">
                <h2 class="text-lg font-semibold mb-4">Completed Appointments</h2>

                @forelse ($completed as $appt)
                    <div class="flex items-center justify-between border rounded-xl p-4 mb-3">
                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 16 16">
                                    <path
                                        d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0" />
                                    <path
                                        d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0z" />
                                </svg>
                            </div>

                            <div>
                                <p class="font-semibold">
                                    {{ $appt->resume->applicant_name }}
                                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">
                                        Completed
                                    </span>
                                    @if ($appt->tag == 2)
                                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700">
                                            Failed
                                        </span>
                                    @elseif ($appt->tag == 1)
                                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-blue-100 text-green-700">
                                            Passed
                                        </span>
                                    @endif
                                </p>

                                <span
                                    class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                                    {{ $appt->meeting_type == 2 ? 'Online' : 'In-person' }}
                                </span>
                                <span class="ml-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100">
                                    @php
                                        $roundLable = [
                                            1 => 'First Round',
                                            2 => '2nd Round',
                                            3 => 'Third Round',
                                            4 => 'Final Round',
                                            5 => 'Other',
                                        ];
                                    @endphp
                                    {{ $roundLable[$appt->interview_round] ?? 'Other' }}
                                </span>
                                <p class="text-sm text-gray-500">
                                    {{ $appt->resume->job->title ?? '—' }}
                                </p>
                                <div class="flex gap-4 text-xs text-gray-500 mt-1">
                                    <span><i
                                            class="bi bi-calendar"></i>{{ $appt->interview_date->format('m/d/Y') }}</span>
                                    <span><i
                                            class="bi bi-clock"></i>{{ Carbon::parse($appt->interview_time)->format('H:i') }}</span>
                                </div>
                                <p class="italic text-sm text-gray-500 mt-1">
                                    {{ $appt->notes }}
                                </p>
                            </div>
                        </div>

                        @if ($appt->tag == 0)
                            <div class="flex gap-2">
                                <button
                                    class="px-3 py-1 text-white inline-flex items-center bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-md text-xs text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                                    <i class="bi bi-calendar3 mr-1"></i>Schedule Next Round
                                </button>

                                <button
                                    class="px-3 py-1 text-white inline-flex items-center bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-md text-xs text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                                    <i class="bi bi-star mr-1"></i>Recommend
                                </button>

                                <button type="button" data-id = "{{ $appt->id }}"
                                    data-applicant-name="{{ $appt->resume->applicant_name }}"
                                    onclick="markAsFailed(this)"
                                    class="px-3 py-1 text-white inline-flex items-center bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-md text-xs text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                                    <i class="bi bi-x-circle mr-1"></i>Failed
                                </button>

                            </div>
                        @endif

                    </div>
                @empty
                    <p class="text-center text-gray-400 py-6">No completed appointments</p>
                @endforelse
            </div>
        @endif
    </div>

    <!-- Create schedule modal -->
    <div id="add-modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto">
            <!-- Modal content -->
            <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
                <!-- Modal header -->
                <div class="flex justify-between items-center pb-4 mb-2 rounded-t border-b sm:mb-5 dark:border-gray-600">
                    <h3 class="text-md font-semibold text-gray-900 dark:text-white">
                        Add New License
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-toggle="add-modal">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="overflow-y-auto max-h-[70vh]">
                    <form action="{{ route('appointment.store') }}" method="POST">
                        @csrf
                        <div class="grid ml-1 mr-1 gap-2 mb-4 sm:grid-cols-2">
                            <div class="w-full md:col-span-2">
                                <label for="company_id"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Company Name*</label>
                                <select name="company_id" id="company_id"
                                    class="select2 bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                    <option value="" selected>Select company</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-full md:col-span-2">
                                <label for="job_id" class="block text-xs font-medium text-gray-900 dark:text-white">Job
                                    Title*</label>
                                <select name="job_id" id="job_id"
                                    class="select2 bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                    <option value="" selected>Select Job</option>
                                </select>
                            </div>

                            <div class="w-full md:col-span-2">
                                <label for="applicant_id"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Applicant Name*</label>
                                <select name="applicant_id" id="applicant_id"
                                    class="select2 bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                    <option value="" selected>Select applicant</option>
                                </select>
                            </div>

                            <div class="sm:col-span-1">
                                <label for="meeting_type"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Meeting Type*</label>
                                <select id="meeting_type" name="meeting_type"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                    <option value="" selected>Select type</option>
                                    <option value="1">In-person</option>
                                    <option value="2">Online</option>
                                </select>
                            </div>

                            <div class="sm:col-span-1">
                                <label for="interview_round"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Interview
                                    Round*</label>
                                <select id="interview_round" name="interview_round"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                    <option value="" selected>Select round</option>
                                    <option value="1">First Round</option>
                                    <option value="2">Second Round</option>
                                    <option value="3">Third Round</option>
                                    <option value="4">Final Round</option>
                                    <option value="5">Other</option>
                                </select>
                            </div>

                            <div class="sm:col-span-1">
                                <label for="interview_date"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Interview
                                    Date*</label>
                                <input type="date" name="interview_date" id="interview_date"
                                    min="{{ date('Y-m-d') }}"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="mm/dd/yyyy" required>
                            </div>

                            <div class="sm:col-span-1">
                                <label for="interview_time"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Interview
                                    Time*</label>
                                <input type="time" name="interview_time" id="interview_time"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="meeting_link"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Meeting Link</label>
                                <input type="text" name="meeting_link" id="meeting_link"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="e.g. https://meet.google.com/abc-defg-hij">
                            </div>

                            <div class="sm:col-span-2">
                                <label for="notes"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Notes</label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="e.g. Interview agenda, topics to discuss, etc."></textarea>
                            </div>

                        </div>
                        <button type="submit"
                            class="text-white inline-flex items-center bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-md text-xs px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                            <svg class="mr-1 -ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Create Appointment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End schedule modal -->

    <!-- Modal  Edit-->
    <div id="edit-modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-2 w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div
                    class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Update Appointment
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-toggle="edit-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="overflow-y-auto max-h-[70vh]">
                    <form id="editForm" class="p-4 md:p-5" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="grid ml-1 mr-1 gap-2 mb-2 sm:grid-cols-2">
                            <div class="w-full md:col-span-2">
                                <label for="edit_company_id"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Company Name*</label>
                                <input type = "text" name="edit_company_id" id="edit_company_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                </input>
                            </div>

                            <div class="w-full md:col-span-2">
                                <label for="edit_job_id"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Job
                                    Title*</label>
                                <input type="text" name="edit_job_id" id="edit_job_id"
                                    class=" bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                </input>
                            </div>

                            <div class="w-full md:col-span-2">
                                <label for="edit_applicant_id"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Applicant Name*</label>
                                <input type="text" name="edit_applicant_id" id="edit_applicant_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                </input>
                            </div>

                            <div class="sm:col-span-1">
                                <label for="edit_meeting_type"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Meeting Type*</label>
                                <select id="edit_meeting_type" name="edit_meeting_type"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                    <option value="" selected>Select type</option>
                                    <option value="1">In-person</option>
                                    <option value="2">Online</option>
                                </select>
                            </div>

                            <div class="sm:col-span-1">
                                <label for="edit_interview_round"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Interview
                                    Round*</label>
                                <select id="edit_interview_round" name="edit_interview_round"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                    <option value="" selected>Select round</option>
                                    <option value="1">First Round</option>
                                    <option value="2">Second Round</option>
                                    <option value="3">Third Round</option>
                                    <option value="4">Final Round</option>
                                    <option value="5">Other</option>
                                </select>
                            </div>

                            <div class="sm:col-span-1">
                                <label for="edit_interview_date"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Interview
                                    Date*</label>
                                <input type="date" name="edit_interview_date" id="edit_interview_date"
                                    min="{{ date('Y-m-d') }}"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="mm/dd/yyyy" required>
                            </div>

                            <div class="sm:col-span-1">
                                <label for="edit_interview_time"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Interview
                                    Time*</label>
                                <input type="time" name="edit_interview_time" id="edit_interview_time"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="edit_meeting_link"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Meeting Link</label>
                                <input type="text" name="edit_meeting_link" id="edit_meeting_link"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="e.g. https://meet.google.com/abc-defg-hij">
                            </div>

                            <div class="sm:col-span-2">
                                <label for="edit_notes"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Notes</label>
                                <textarea name="edit_notes" id="edit_notes" rows="3"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="e.g. Interview agenda, topics to discuss, etc."></textarea>
                            </div>
                        </div>

                        <button type="submit"
                            class="mt-2 text-white inline-flex items-center bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-md text-xs px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                            {{-- <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg> --}}
                            Update Appointment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End edit modal -->


    <script>
        function clearModalFields() {
            // Clear all form fields
            const form = document.querySelector('form');
            form.reset();

            // Remove any success messages after a delay
            setTimeout(() => {
                const successMessage = document.querySelector('[data-success]');
                if (successMessage) {
                    successMessage.remove();
                }
            }, 3000);
        }

        function editAppointment(button) {
            const id = button.getAttribute('data-id');
            const resumeId = button.getAttribute('data-resume-id');
            const companyName = button.getAttribute('data-company-name');
            const jobTitle = button.getAttribute('data-job-title');
            const applicantName = button.getAttribute('data-applicant-name');
            const interviewDate = button.getAttribute('data-interview-date');
            const interviewTime = button.getAttribute('data-interview-time');
            const interviewRound = button.getAttribute('data-interview-round');
            const meetingLink = button.getAttribute('data-meeting-link');
            const meetingType = button.getAttribute('data-meeting-type');
            const notes = button.getAttribute('data-notes');
            const status = button.getAttribute('data-status');

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_company_id').value = companyName;
            document.getElementById('edit_job_id').value = jobTitle;
            document.getElementById('edit_applicant_id').value = applicantName;
            document.getElementById('edit_meeting_type').value = meetingType;
            document.getElementById('edit_interview_round').value = interviewRound;
            document.getElementById('edit_interview_date').value = interviewDate;
            document.getElementById('edit_interview_time').value = interviewTime;
            document.getElementById('edit_meeting_link').value = meetingLink;
            document.getElementById('edit_notes').value = notes;

            // Set form action
            const form = document.getElementById('editForm');
            form.action = `/appointment/${id}`;
        }

        document.getElementById('company_id').addEventListener('change', function() {
            const companySelect = this.value;
            if (companySelect) {
                fetch(`/api/companies/${companySelect}/jobs`)
                    .then(response => response.json())
                    .then(data => {
                        const jobSelect = document.getElementById('job_id');
                        jobSelect.innerHTML = '<option value="" selected>Select Job</option>';
                        data.forEach(job => {
                            const option = document.createElement('option');
                            option.value = job.id;
                            option.textContent = job.title;
                            jobSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching jobs:', error));
            } else {
                document.getElementById('job_id').innerHTML = '<option value="" selected>Select Job</option>';
            }
        });

        document.getElementById('job_id').addEventListener('change', function() {
            const jobSelect = this.value;
            if (jobSelect) {
                fetch(`/api/jobs/${jobSelect}/applicants`)
                    .then(response => response.json())
                    .then(data => {
                        const applicantSelect = document.getElementById('applicant_id');
                        applicantSelect.innerHTML = '<option value="" selected>Select Applicant</option>';
                        data.forEach(applicant => {
                            const option = document.createElement('option');
                            option.value = applicant.id;
                            option.textContent =
                                `${applicant.applicant_name} - ${applicant.match_percentage ? `(${applicant.match_percentage}%)` : ''}`;
                            applicantSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching applicants:', error));
            } else {
                document.getElementById('applicant_id').innerHTML =
                    '<option value="" selected>Select Applicant</option>';
            }
        });

        function markAsComplete(button) {
            const id = button.getAttribute('data-id');
            const applicantName = button.getAttribute('data-applicant-name');

            if (confirm(`Are you sure you want to mark the appointment with ${applicantName} as complete?`)) {
                fetch(`/appointment/${id}/complete`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Optionally, show a success message or update the UI
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            alert('Failed to mark appointment as complete. Please try again.');
                        }
                    })
                    .catch(error => console.error('Error marking appointment as complete:', error));
            }
        }

        function markAsFailed(button) {
            const id = button.getAttribute('data-id');
            const applicantName = button.getAttribute('data-applicant-name');

            if (confirm(`Are you sure you want to mark the appointment with ${applicantName} as failed?`)) {
                fetch(`/appointment/${id}/fail`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Optionally, show a success message or update the UI
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            alert('Failed to mark appointment as failed. Please try again.');
                        }
                    })
                    .catch(error => console.error('Error marking appointment as failed:', error));
            }
        }
    </script>
@endsection
