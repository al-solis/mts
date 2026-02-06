@extends('dashboard')
@section('content')
    <div class="p-6 space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">AI Resume Matching</h1>
                <p class="text-sm text-gray-500">
                    Upload and Match Resumes with Job Requirements.
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

                {{-- <button data-modal-target="add-modal" data-modal-toggle="add-modal"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Company
                </button> --}}
            </div>
        </div>

        {{-- Alerts --}}
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

        {{-- Job Selection --}}
        <div class="bg-white p-4 rounded shadow">
            <label class="block text-sm font-medium mb-1">Select Job Posting</label>
            <select id="job_id" class="w-full border rounded px-3 py-2">
                <option value="">-- Select Job --</option>
                @foreach ($jobs as $job)
                    <option value="{{ $job->id }}">{{ $job->title }} ({{ $job->company->name }})</option>
                @endforeach
            </select>
        </div>

        {{-- Resume Upload --}}
        <div class="bg-white p-4 rounded shadow">
            <form id="resumeForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="job_id" id="selected_job">


                <label class="block text-sm font-medium mb-1">Upload Resumes (PDF / DOCX)</label>
                <input type="file" name="resumes[]" id="resumes" multiple accept=".pdf,.doc,.docx,.txt"
                    class="w-full border rounded px-3 py-2" />

                <button type="submit" id="uploadBtn" disabled
                    class="mt-3 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Upload & Match
                </button>
            </form>
        </div>

        <form action="" method="GET">
            <div class="flex flex-col md:flex-row gap-2 text-xs md:text-sm">
                <div class="md:w-2/3 w-full">
                    <input type="text" id="simple-search" name="search" placeholder="Search by name or description..."
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                        value = "{{ request()->query('search') }}" oninput="this.form.submit()">
                </div>

                <div class="md:w-1/3 w-full">
                    <select id="status" name="status"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                        onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            <button type="submit"
                class="hidden mt-4 w-full shrink-0 rounded-lg bg-gray-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 sm:mt-0 sm:w-auto">Search</button>
        </form>

        {{-- Table --}}
        <div class="bg-white p-4 rounded shadow">
            <h2 class="text-lg font-semibold mb-3">Matching Results</h2>


            <table class="w-full border text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-2 py-1">Applicant</th>
                        <th class="border px-2 py-1">Job Title</th>
                        <th class="border px-2 py-1">Education</th>
                        <th class="border px-2 py-1">Experience</th>
                        <th class="border px-2 py-1">Skills</th>
                        <th class="border px-2 py-1">Certifications</th>
                        <th class="border px-2 py-1">Match %</th>
                    </tr>
                </thead>
                <tbody id="resultsBody">
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-400">No results yet</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    </div>

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

        const jobSelect = document.getElementById('job_id');
        const resumeInput = document.getElementById('resumes');
        const uploadBtn = document.getElementById('uploadBtn');
        const selectedJob = document.getElementById('selected_job');
        const resultsBody = document.getElementById('resultsBody');


        jobSelect.addEventListener('change', function() {
            if (this.value) {
                resumeInput.disabled = false;
                uploadBtn.disabled = false;
                selectedJob.value = this.value;
            } else {
                resumeInput.disabled = true;
                uploadBtn.disabled = true;
            }
        });


        document.getElementById('resumeForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            uploadBtn.innerText = 'Processing...';
            uploadBtn.disabled = true;


            fetch("{{ route('resume.upload.match') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    resultsBody.innerHTML = '';


                    data.forEach(row => {
                        resultsBody.innerHTML += `
                            <tr>
                            <td class="border px-2 py-1">${row.applicant}</td>
                            <td class="border px-2 py-1">${row.job}</td>
                            <td class="border px-2 py-1">${row.education}%</td>
                            <td class="border px-2 py-1">${row.experience}%</td>
                            <td class="border px-2 py-1">${row.skills}%</td>
                            <td class="border px-2 py-1">${row.certifications}%</td>
                            <td class="border px-2 py-1 font-semibold">${row.match}%</td>
                            </tr>
                            `;
                    });


                    uploadBtn.innerText = 'Upload & Match';
                    uploadBtn.disabled = false;
                });
        });
    </script>
@endsection
