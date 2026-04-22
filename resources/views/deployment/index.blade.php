@extends('dashboard')
@section('content')
    @php
        use Illuminate\Support\Str;
        use Carbon\Carbon;
    @endphp

    <div class="p-6 space-y-6  bg-gray-100">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Deployment Management</h1>
                <p class="text-sm text-gray-500">
                    Track and Manage deployed applicants to companies.
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
                    Deploy Applicant
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        @php
            $cards = [
                [
                    'title' => 'Total Deployments',
                    'value' => $totalDeployments,
                    'color' => 'gray',
                    'icon' => '
                        <svg xmlns="http://www.w3.org/2000/svg" class = "w-5 h-5 text-gray-600" width="16" height="16" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                        </svg>',
                ],
                [
                    'title' => 'Active Placements',
                    'value' => $activePlacements,
                    'color' => 'blue',
                    'icon' => '
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" width="16" height="16"
                        class="bi bi-person-fill-check" viewBox="0 0 16 16">                            
                            <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                        <path d="M2 13c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4"/>
                        </svg>',
                ],
                [
                    'title' => 'Companies Served',
                    'value' => $companiesServed,
                    'color' => 'indigo',
                    'icon' => '
                        <svg class="w-5 h-5 text-indigo-600" width="16" height="16" fill="currentColor" class="bi bi-building-check" viewBox="0 0 16 16">
                        <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514"/>
                        <path d="M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6.5a.5.5 0 0 1-1 0V1H3v14h3v-2.5a.5.5 0 0 1 .5-.5H8v4H3a1 1 0 0 1-1-1z"/>
                        <path d="M4.5 2a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm3 0a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm3 0a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm-6 3a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm3 0a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm3 0a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm-6 3a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm3 0a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
                        </svg>',
                ],
                [
                    'title' => 'Available for Deployment',
                    'value' => $availableForDeployment,
                    'color' => 'green',
                    'icon' => '
                        <svg class="w-5 h-5 text-green-600" width="16" height="16" fill="currentColor" class="bi bi-person-check-fill" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M15.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L12.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                        <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
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
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </div>
            <button type="submit"
                class="hidden mt-4 w-full shrink-0 rounded-lg bg-gray-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 sm:mt-0 sm:w-auto">Search</button>
        </form>

        <div class="bg-white rounded-xl border p-4">
            <h2 class="text-lg font-semibold mb-4">All Deployments</h2>

            @forelse ($deployments as $deploy)
                <div
                    class="flex items-center justify-between bg-white border border-gray-200 rounded-xl border-l-4 border-l-indigo-500 p-4 mb-3 shadow-sm hover:shadow-md transition">

                    <div class="flex items-center gap-3">
                        @php
                            $status = [
                                1 => ['text' => 'Active', 'color' => 'green'],
                                0 => ['text' => 'Inactive', 'color' => 'gray'],
                                2 => ['text' => 'Cancelled', 'color' => 'red'],
                            ];
                        @endphp
                        <div class="flex-shrink-0">
                            <img src="{{ $deploy->resume->photo ? '/storage/' . $deploy->resume->photo : '/images/avatar.png' }}"
                                class="w-12 h-12 rounded-full object-cover">
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">
                                {{ ucwords(strtolower($deploy->resume->applicant_name)) ?? 'Unknown Applicant' }}
                                <span
                                    class="ml-2 px-2 py-0.5 text-xs rounded-full bg-{{ $status[$deploy->status]['color'] }}-100 text-{{ $status[$deploy->status]['color'] }}-700">
                                    {{ $status[$deploy->status]['text'] }}
                                </span>
                            </p>

                            <p class="text-sm text-gray-500">
                                <i class="bi bi-briefcase"></i> {{ $deploy->resume->job->title ?? '—' }}

                                <span class="ml-2 px-2 py-0.5 text-sm">
                                    <i class="bi bi-building"></i> {{ $deploy->resume->job->company->name ?? '—' }}
                                </span>

                                <span class="ml-2 px-2 py-0.5 text-sm">
                                    <i class="bi bi-calendar"></i> {{ $deploy->created_at->format('m/d/Y') }}
                            </p>

                            {{-- <div class="flex gap-4 text-xs text-gray-500 mt-1">
                                <span><i class="bi bi-calendar"></i> {{ $deploy->interview_date->format('m/d/Y') }}</span>
                                <span><i class="bi bi-clock"></i>
                                    {{ Carbon::parse($deploy->interview_time)->format('H:i') }}</span>
                            </div> --}}

                            <p class="italic text-sm text-gray-500 mt-1">
                                {{ $deploy->notes }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" data-modal-target="edit-modal" data-modal-toggle="edit-modal"
                            data-id = "{{ $deploy->id }}" data-resume-id = "{{ $deploy->resume_id }}"
                            data-company-name = "{{ $deploy->resume->job->company->name ?? '' }}"
                            data-job-title = "{{ $deploy->resume->job->title ?? '' }}"
                            data-applicant-name = "{{ $deploy->resume->applicant_name }}"
                            data-salary = "{{ $deploy->salary }}" data-agency-fee = "{{ $deploy->agency_fee }}"
                            data-start-date = "{{ $deploy->start_date ? Carbon::parse($deploy->start_date)->format('Y-m-d') : '' }}"
                            data-end-date = "{{ $deploy->end_date ? Carbon::parse($deploy->end_date)->format('Y-m-d') : '' }}"
                            data-notes = "{{ $deploy->notes }}" data-status = "{{ $deploy->status }}"
                            onclick="editDeployment(this)" class="px-3 py-1 text-xs border rounded-lg hover:bg-gray-100">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-400 py-6">No deployed applicants yet.</p>
            @endforelse
        </div>

        {{-- @if ($completedAppointments !== 0)
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
                                <button type="button" data-id = "{{ $appt->id }}"
                                    data-applicant-name="{{ $appt->resume->applicant_name }}"
                                    onclick="scheduleNextRound(this)"
                                    class="px-3 py-1 text-white inline-flex items-center bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-md text-xs text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                                    <i class="bi bi-calendar3 mr-1"></i>Schedule Next Round
                                </button>

                                <button type="button" data-id = "{{ $appt->id }}"
                                    data-applicant-name="{{ $appt->resume->applicant_name }}"
                                    onclick="markAsPassed(this)"
                                    class="px-3 py-1 text-white inline-flex items-center bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-md text-xs text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                                    <i class="bi bi-check2-circle mr-1"></i>Passed
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
        @endif --}}
    </div>

    <!-- Deploy applicant modal -->
    <div id="add-modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full">
        <div class="relative p-2 w-full max-w-md h-full md:h-auto">
            <!-- Modal content -->
            <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
                <div class="pb-4 mb-2 rounded-t border-b sm:mb-5 dark:border-gray-600">
                    <div class="flex justify-between items-center">
                        <h3 class="text-md font-semibold text-gray-900 dark:text-white">
                            Deploy Applicant
                        </h3>

                        <button type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-toggle="add-modal">
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Select a passed applicant to deploy them to a company.
                    </p>

                </div>

                <!-- Modal body -->
                <div class="overflow-y-auto max-h-[70vh]">
                    <form action="{{ route('deployment.store') }}" method="POST">
                        @csrf
                        <div class="grid ml-1 mr-1 gap-2 mb-4 sm:grid-cols-2">
                            <div class="w-full md:col-span-2">
                                <label for="applicant_id"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Applicant Name*</label>
                                <select name="applicant_id" id="applicant_id"
                                    class="select2 bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                    <option value="" selected>Select applicant</option>
                                    @foreach ($passedApplicants as $applicant)
                                        <option value="{{ $applicant->id }}">{{ $applicant->applicant_name }} -
                                            {{ $applicant->job->title ?? '—' }} at
                                            {{ $applicant->job->company->name ?? '—' }}</option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="sm:col-span-1">
                                <label for="salary"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Salary*</label>
                                <input type="number" id="salary" name="salary"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="25000" required>
                                </input>
                            </div>

                            <div class="sm:col-span-1">
                                <label for="agency_fee"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Agency Fee*</label>
                                <input type="number" name="agency_fee" id="agency_fee" min="0"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="1000" required>
                            </div>

                            <div class="sm:col-span-1">
                                <label for="start_date"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Start Date</label>
                                <input type="date" name="start_date" id="start_date"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="mm/dd/yyyy">
                            </div>

                            <div class="sm:col-span-1">
                                <label for="end_date" class="block text-xs font-medium text-gray-900 dark:text-white">End
                                    Date</label>
                                <input type="date" name="end_date" id="end_date" min="{{ date('Y-m-d') }}"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="mm/dd/yyyy">
                            </div>

                            <div class="sm:col-span-2">
                                <label for="notes"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Notes</label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="e.g. On-boarding procedure and other info."></textarea>
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
                            Deploy Applicant
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
                                <label for="edit_applicant_name"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Applicant Name*</label>
                                <input type = "text" name="edit_applicant_name" id="edit_applicant_name"
                                    class="select2 bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                </input>
                            </div>

                            <div class="w-full md:col-span-2">
                                <label for="edit_company_name"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Company Name*</label>
                                <input type = "text" name="edit_company_name" id="edit_company_name"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                </input>
                            </div>

                            <div class="w-full md:col-span-2">
                                <label for="edit_job_title"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Job Title*</label>
                                <input type = "text" name="edit_job_title" id="edit_job_title"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    required>
                                </input>
                            </div>

                            <div class="sm:col-span-1">
                                <label for="edit_salary"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Salary*</label>
                                <input type="number" id="edit_salary" name="edit_salary"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="25000" required>
                                </input>
                            </div>

                            <div class="sm:col-span-1">
                                <label for="edit_agency_fee"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Agency Fee*</label>
                                <input type="number" name="edit_agency_fee" id="edit_agency_fee" min="0"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="1000" required>
                            </div>

                            <div class="sm:col-span-1">
                                <label for="edit_start_date"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Start Date</label>
                                <input type="date" name="edit_start_date" id="edit_start_date"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="mm/dd/yyyy">
                            </div>

                            <div class="sm:col-span-1">
                                <label for="edit_end_date"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">End
                                    Date</label>
                                <input type="date" name="edit_end_date" id="edit_end_date" min="{{ date('Y-m-d') }}"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="mm/dd/yyyy">
                            </div>

                            <div class="sm:col-span-2">
                                <label for="edit_status"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Status</label>
                                <select name="edit_status" id="edit_status"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                    <option value="2">Cancelled</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="edit_notes"
                                    class="block text-xs font-medium text-gray-900 dark:text-white">Notes</label>
                                <textarea name="edit_notes" id="edit_notes" rows="3"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                    placeholder="e.g. On-boarding procedure and other info."></textarea>
                            </div>
                        </div>

                        <button type="submit"
                            class="mt-2 text-white inline-flex items-center bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-md text-xs px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                            {{-- <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg> --}}
                            Update Deployment
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

        function editDeployment(button) {
            const id = button.getAttribute('data-id');
            const resumeId = button.getAttribute('data-resume-id');
            const companyName = button.getAttribute('data-company-name');
            const jobTitle = button.getAttribute('data-job-title');
            const applicantName = button.getAttribute('data-applicant-name');
            const startDate = button.getAttribute('data-start-date');
            const endDate = button.getAttribute('data-end-date');
            const salary = button.getAttribute('data-salary');
            const agencyFee = button.getAttribute('data-agency-fee');
            const notes = button.getAttribute('data-notes');
            const status = button.getAttribute('data-status');

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_company_name').value = companyName;
            document.getElementById('edit_job_title').value = jobTitle;
            document.getElementById('edit_applicant_name').value = applicantName;
            document.getElementById('edit_start_date').value = startDate;
            document.getElementById('edit_end_date').value = endDate;
            document.getElementById('edit_salary').value = salary;
            document.getElementById('edit_agency_fee').value = agencyFee;
            document.getElementById('edit_notes').value = notes;
            document.getElementById('edit_status').value = status;

            // Set form action
            const form = document.getElementById('editForm');
            form.action = `/deployment/${id}`;
        }
    </script>
@endsection
