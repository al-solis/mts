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
                                    {{ $appt->interview_round }}
                                </span>
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
                            data-interview-date = "{{ $appt->interview_date->format('Y-m-d') }}"
                            data-interview-time = "{{ Carbon::parse($appt->interview_time)->format('H:i') }}"
                            data-interview-round = "{{ $appt->interview_round }}"
                            data-meeting-link = "{{ $appt->meeting_link }}" data-notes = "{{ $appt->notes }}"
                            data-status = "{{ $appt->status }}"
                            class="px-3 py-1 text-xs border rounded-lg hover:bg-gray-100">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>

                        <form action="" method="POST">
                            @csrf
                            <button
                                class="px-3 py-1 text-white inline-flex items-center bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-md text-xs text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                                <i class="bi bi-check-circle"></i><span class="ml-1">Complete</span>
                            </button>
                        </form>

                        <form action="" method="POST">
                            @csrf
                            <button class="px-3 py-1 text-xs text-red-600 border border-red-300 rounded-lg">
                                <i class="bi bi-x-circle"></i> Cancel
                            </button>
                        </form>
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
                                        d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647z" />
                                </svg>
                            </div>

                            <div>
                                <p class="font-semibold">
                                    {{ $appt->resume->file_name }}
                                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">
                                        Completed
                                    </span>
                                </p>

                                <p class="text-xs text-gray-500">
                                    {{ $appt->interview_round }}
                                </p>

                                <div class="flex gap-4 text-xs text-gray-500 mt-1">
                                    <span>📅 {{ $appt->interview_date->format('m/d/Y') }}</span>
                                    <span>⏰ {{ Carbon::parse($appt->interview_time)->format('H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button class="px-3 py-1 text-xs bg-gray-900 text-white rounded-lg">
                                📅 Schedule Next Round
                            </button>

                            <button class="px-3 py-1 text-xs bg-gray-900 text-white rounded-lg">
                                👍 Recommend
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-400 py-6">No completed appointments</p>
                @endforelse
            </div>
        @endif
    </div>



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
                            <div class="sm:col-span-2">
                                <label for="edit_position"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Position*</label>
                                <input type="text" name="edit_position" id="edit_position"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="e.g. SANAI Digital Solutions Inc." required>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="edit_industry"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Industry*</label>
                                <input type="text" name="edit_industry" id="edit_industry"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="e.g. Information Technology" required>
                            </div>

                            <div class="md:col-span-2">
                                <label for="edit_contact_person"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Contact Person*</label>
                                <input type="text" name="edit_contact_person" id="edit_contact_person"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="e.g. Juan Dela Cruz" required>
                            </div>
                            <div class="md:col-span-2">
                                <label for="edit_email"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Email Address*</label>
                                <input type="email" name="edit_email" id="edit_email"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="e.g. juan.delacruz@yahoo.com" required>
                            </div>
                            <div class="md:col-span-1">
                                <label for="edit_location"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Location*</label>
                                <input type="text" name="edit_location" id="edit_location"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="e.g. Imus, Cavite" required>
                            </div>

                            <div class="md:col-span-1">
                                <label for="edit_status"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Status*</label>
                                <select id="edit_status" name="edit_status"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                    {{-- <option selected="">Select product type</option> --}}
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit"
                            class="mt-2 text-white inline-flex items-center bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-md text-xs px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                            {{-- <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg> --}}
                            Update Company
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
    </script>
@endsection
