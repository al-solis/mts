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
            {{-- Job info container --}}
            <div id="jobInfoContainer" class="mt-2"></div>
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
                        <th class="border px-2 py-1">Relevance</th>
                        <th class="border px-2 py-1">General</th>
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
            const form = document.querySelector('form');
            form.reset();

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

        // Function to load existing resumes for a job
        function loadJobResumes(jobId) {
            if (!jobId) {
                resultsBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4 text-gray-400">
                        Select a job to view previous results
                    </td>
                </tr>
            `;
                return;
            }

            // Show loading state
            resultsBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                    <p class="mt-2 text-gray-600">Loading previous results...</p>
                </td>
            </tr>
        `;

            // Fetch resumes for this job
            fetch(`/resume/by-job/${jobId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(async res => {
                    if (!res.ok) {
                        throw new Error('Failed to load job resumes');
                    }
                    return res.json();
                })
                .then(data => {
                    updateResultsTable(data.success || []);

                    // Add count indicator
                    if (data.count > 0) {
                        const jobInfo = document.getElementById('jobInfo');
                        if (jobInfo) {
                            jobInfo.innerHTML = `
                        <div class="bg-blue-50 border border-blue-200 rounded px-3 py-2 text-sm">
                            Loaded ${data.count} previous resume(s) for this job
                        </div>
                    `;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading job resumes:', error);
                    resultsBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4 text-red-600">
                        Failed to load previous results. ${error.message}
                    </td>
                </tr>
            `;
                });
        }

        // Function to update the results table
        function updateResultsTable(resumes) {
            resultsBody.innerHTML = '';

            if (resumes.length === 0) {
                resultsBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4 text-gray-400">
                    No results found. Upload resumes to see matching results.
                </td>
            </tr>
        `;
                return;
            }

            resumes.forEach(row => {
                // Safely handle null/undefined values
                const education = parseFloat(row.education) || 0;
                const experience = parseFloat(row.experience) || 0;
                const relevance = parseFloat(row.relevance) || 0;
                const general = parseFloat(row.general) || 0;
                const match = parseFloat(row.match) || 0;
                const passingThreshold = parseFloat(row.passing_threshold) || 70;

                const statusClass = row.status === 'Passed' ?
                    'bg-green-100 text-green-800 border-green-200' :
                    'bg-red-100 text-red-800 border-red-200';

                const statusIcon = row.status === 'Passed' ? '✓' : '✗';

                resultsBody.innerHTML += `
            <tr class="hover:bg-gray-50">
                <td class="border px-3 py-2">
                    <div class="font-medium">${row.applicant || 'Unknown'}</div>
                    <div class="text-xs text-gray-500">${row.created_at || ''}</div>
                </td>
                <td class="border px-3 py-2">${row.job || 'Unknown Job'}</td>
                <td class="border px-3 py-2 text-center">${education.toFixed(2)}%</td>
                <td class="border px-3 py-2 text-center">${experience.toFixed(2)}%</td>
                <td class="border px-3 py-2 text-center">${relevance.toFixed(2)}%</td>
                <td class="border px-3 py-2 text-center">${general.toFixed(2)}%</td>
                <td class="border px-3 py-2 text-center font-semibold ${match >= passingThreshold ? 'text-green-600' : 'text-red-600'}">
                    ${match.toFixed(2)}%
                </td>
                <td class="border px-3 py-2 text-center">
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium ${statusClass}">
                        ${statusIcon} ${row.status || 'Unknown'}
                    </span>
                </td>
            </tr>
        `;
            });

            // Add summary row
            const passedCount = resumes.filter(row => row.status === 'Passed').length;
            const totalCount = resumes.length;

            resultsBody.innerHTML += `
        <tr class="bg-gray-50 font-medium">
            <td colspan="6" class="border px-3 py-2 text-right">
                Summary:
            </td>
            <td class="border px-3 py-2 text-center">
                ${passedCount}/${totalCount} Passed
            </td>
            <td class="border px-3 py-2 text-center">
                <span class="px-2 py-1 rounded ${passedCount === totalCount ? 'bg-green-100 text-green-800' : passedCount > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">
                    ${Math.round((passedCount / totalCount) * 100)}% Pass Rate
                </span>
            </td>
        </tr>
    `;
        }

        // Event listener for job selection
        jobSelect.addEventListener('change', function() {
            const jobId = this.value;

            if (jobId) {
                resumeInput.disabled = false;
                uploadBtn.disabled = false;
                selectedJob.value = jobId;

                // Load existing resumes for this job
                loadJobResumes(jobId);

                // Update job info display
                const selectedOption = this.options[this.selectedIndex];
                const jobInfo = document.getElementById('jobInfo');
                if (!jobInfo) {
                    // Create job info display if it doesn't exist
                    const jobSelectionDiv = document.querySelector('.bg-white.p-4.rounded.shadow:first-child');
                    const infoDiv = document.createElement('div');
                    infoDiv.id = 'jobInfo';
                    infoDiv.className = 'mt-2 text-sm';
                    jobSelectionDiv.appendChild(infoDiv);
                }
            } else {
                resumeInput.disabled = true;
                uploadBtn.disabled = true;
                resultsBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4 text-gray-400">
                        Select a job to view previous results
                    </td>
                </tr>
            `;

                // Clear job info
                const jobInfo = document.getElementById('jobInfo');
                if (jobInfo) {
                    jobInfo.innerHTML = '';
                }
            }
        });

        // Load resumes on page load if a job is already selected
        document.addEventListener('DOMContentLoaded', function() {
            const initialJobId = jobSelect.value;
            if (initialJobId) {
                loadJobResumes(initialJobId);
            }
        });

        // Update the form submission handler to reload after upload
        document.getElementById('resumeForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const uploadBtn = document.getElementById('uploadBtn');
            const currentJobId = selectedJob.value;

            uploadBtn.innerText = 'Processing...';
            uploadBtn.disabled = true;

            // Show loading state
            resultsBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                    <p class="mt-2 text-gray-600">Processing resumes...</p>
                </td>
            </tr>
        `;

            fetch("{{ route('resume.upload.match') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(async res => {
                    if (!res.ok) {
                        throw new Error('Upload failed with status: ' + res.status);
                    }
                    return res.json();
                })
                .then(data => {
                    console.log('Response data:', data);

                    // Reload the resumes for this job to show updated list
                    loadJobResumes(currentJobId);

                    // Show success notification
                    showNotification('Successfully processed ' + data.summary.processed + ' resume(s)',
                        'success');

                    // Clear file input
                    document.getElementById('resumes').value = '';

                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Upload failed: ' + error.message, 'error');
                })
                .finally(() => {
                    uploadBtn.innerText = 'Upload & Match';
                    uploadBtn.disabled = false;
                });
        });

        // Notification function
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg ${
            type === 'success' ? 'bg-green-100 text-green-800 border-green-200' :
            type === 'error' ? 'bg-red-100 text-red-800 border-red-200' :
            'bg-blue-100 text-blue-800 border-blue-200'
        } border`;
            notification.innerHTML = `
            <div class="flex items-center gap-3">
                <span class="font-medium">${type === 'success' ? '✓' : type === 'error' ? '✗' : 'ℹ'}</span>
                <span>${message}</span>
            </div>
        `;

            document.body.appendChild(notification);

            // Remove after 5 seconds
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        // Event listener for job selection
        jobSelect.addEventListener('change', function() {
            const jobId = this.value;
            const jobInfoContainer = document.getElementById('jobInfoContainer');

            if (jobId) {
                resumeInput.disabled = false;
                uploadBtn.disabled = false;
                selectedJob.value = jobId;

                // Load existing resumes for this job
                loadJobResumes(jobId);

                // Update job info display
                const selectedOption = this.options[this.selectedIndex];
                const jobText = selectedOption.textContent;

                if (jobInfoContainer) {
                    jobInfoContainer.innerHTML = `
                <div id="jobInfo" class="text-sm">
                    <div class="bg-blue-50 border border-blue-200 rounded px-3 py-2">
                        <div class="font-medium">Selected Job:</div>
                        <div class="mt-1">${jobText}</div>
                        <div id="jobCountInfo" class="mt-1 text-xs text-blue-600"></div>
                    </div>
                </div>
            `;
                }
            } else {
                resumeInput.disabled = true;
                uploadBtn.disabled = true;
                resultsBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4 text-gray-400">
                    Select a job to view previous results
                </td>
            </tr>
        `;

                // Clear job info
                if (jobInfoContainer) {
                    jobInfoContainer.innerHTML = '';
                }
            }
        });
    </script>
@endsection
