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
        <div class="bg-white p-2 rounded-xl shadow">
            <div class="ml-2 mr-2">
                <label class="block text-lg font-semibold mb-1">Select Job Posting</label>
                <select id="job_id"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500">
                    <option value="">-- Select Job --</option>
                    @foreach ($jobs as $job)
                        <option value="{{ $job->id }}">{{ $job->title }} ({{ $job->company->name }})</option>
                    @endforeach
                </select>
                {{-- Job info container --}}
                <div id="jobInfoContainer" class="mt-2"></div>
            </div>
        </div>

        {{-- Resume Upload --}}
        <div class="bg-white p-2 rounded-xl shadow">
            <div class="ml-2 mr-2">
                <form id="resumeForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="job_id" id="selected_job">


                    <label class="block text-lg font-semibold mb-1">Upload Resumes (PDF / DOC / DOCX)</label>
                    <input type="file" name="resumes[]" id="resumes" multiple accept=".pdf,.doc,.docx,.txt"
                        class="w-full border rounded px-3 py-2 inline-flex items-center gap-2 text-xs font-medium text-gray bg-white-100 hover:bg-white-800" />

                    <button type="submit" id="uploadBtn" disabled
                        class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-xs font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800">
                        Upload & Match
                    </button>
                </form>
            </div>
        </div>

        {{-- <form action="" method="GET">
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
        </form> --}}

        {{-- Table --}}
        <div class="bg-white p-2 rounded-xl shadow">
            <div class="ml-2 mr-2">
                <h2 class="text-lg font-semibold mb-3">Matching Results</h2>

                <div id="resultsBody" class="space-y-4">
                    <div class="text-center py-6 text-gray-400">
                        No results yet
                    </div>
                </div>
            </div>
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
            const resultsBody = document.getElementById('resultsBody');
            resultsBody.innerHTML = '';

            if (resumes.length === 0) {
                resultsBody.innerHTML = `
            <div class="text-center py-6 text-gray-400">
                No results found. Upload resumes to see matching results.
            </div>
        `;
                return;
            }

            resumes.forEach((row, index) => {
                const match = parseFloat(row.match) || 0;
                const threshold = parseFloat(row.passing_threshold) || 70;

                const matchLabel =
                    match >= 85 ? 'Excellent Match' :
                    match >= 70 ? 'Good Match' :
                    'Low Match';

                const isPassed = match >= threshold;

                const matchColor = isPassed ? 'text-green-600' :
                    match >= 70 ?
                    'text-blue-600' :
                    'text-red-600';

                const passButton = isPassed && row.tag == 0 ?
                    `
                    <button class="inline-flex items-center gap-1 px-3 py-1 text-xs text-green-600 border border-green-200 rounded-lg hover:bg-green-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-square" viewBox="0 0 16 16">
                            <path d="M3 14.5A1.5 1.5 0 0 1 1.5 13V3A1.5 1.5 0 0 1 3 1.5h8a.5.5 0 0 1 0 1H3a.5.5 0 0 0-.5.5v10a.5.5 0 0 0 .5.5h10a.5.5 0 0 0 .5-.5V8a.5.5 0 0 1 1 0v5a1.5 1.5 0 0 1-1.5 1.5z"/>
                            <path d="m8.354 10.354 7-7a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0"/>
                        </svg>
                        Pass
                    </button>
                    ` :
                    '';

                const tagLabel = row.tag == 1 ? 'Scheduled' :
                    row.tag == 2 ? 'Passed' :
                    row.tag == 3 ? 'Hold' :
                    row.tag == 4 ? 'Rejected' :
                    'Pending';

                const tagColor = row.tag == 1 ? 'bg-blue-300 text-blue-800 border-blue-400' :
                    row.tag == 2 ? 'bg-green-300 text-green-800 border-green-400' :
                    row.tag == 3 ? 'bg-yellow-300 text-yellow-800 border-yellow-400' :
                    row.tag == 4 ? 'bg-red-300 text-red-800 border-red-400' :
                    'bg-gray-300 text-gray-800 border-gray-400';

                const scheduleBtn = row.tag == 0 ?
                    `<button type="button"                             
                            onclick="scheduleInterview(${index}, ${row.id}, '${row.applicant}')"
                            class="inline-flex items-center gap-1 px-3 py-1 text-xs border rounded-lg hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2-check" viewBox="0 0 16 16">
                            <path d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5z"/>
                            </svg>
                            Schedule
                            </button>` :
                    row.tag == 1 ?
                    `<button type="button" 
                            data-id="${row.id}"
                            data-applicant-name="${row.applicant}"
                            onclick="markAsPassed(this)"
                            class="inline-flex items-center gap-1 px-3 py-1 text-xs text-green-600 border border-green-200 rounded-lg hover:bg-green-50">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-square" viewBox="0 0 16 16">
                                <path d="M3 14.5A1.5 1.5 0 0 1 1.5 13V3A1.5 1.5 0 0 1 3 1.5h8a.5.5 0 0 1 0 1H3a.5.5 0 0 0-.5.5v10a.5.5 0 0 0 .5.5h10a.5.5 0 0 0 .5-.5V8a.5.5 0 0 1 1 0v5a1.5 1.5 0 0 1-1.5 1.5z"/>
                                <path d="m8.354 10.354 7-7a.5.5 0 0 0-.708-.708L8 9.293l-2.646-2.647a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0"/>
                            </svg>
                            Mark as Passed
                            </button>` : '';

                resultsBody.innerHTML += `
                <div class="border rounded-xl p-4 shadow-sm bg-white">
                    <div class="flex justify-between items-start">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-gray-100 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
                                <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                                <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                                </svg>
                            </div>
                            <div>                                
                                <div class="sm-col-span-1 font-semibold text-gray-900">
                                    ${row.applicant || 'Unknown Applicant'}
                                </div>                                 
                                <div class="grid sm:grid-cols-1 md:grid-cols-2 gap-1">
                                    <div class="text-sm ${matchColor} mr-2">
                                        ${match.toFixed(0)}% Match - ${matchLabel}
                                    </div>
                                    <div class="flex justify-start">
                                        <div class="inline-flex items-center justify-center 
                                                    ${tagColor} text-xs font-semibold 
                                                    rounded-full text-white px-2 py-0.5 w-fit">
                                            ${tagLabel}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    ${row.created_at || ''}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button onclick="toggleDetails(${index})"
                                class="inline-flex items-center gap-1 px-3 py-1 text-xs border rounded-lg hover:bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                </svg> 
                                Details
                            </button>

                            ${scheduleBtn}
                            ${passButton}                      

                            <button type="button" 
                            data-id="${row.id}"
                            onclick="deleteMatch(${row.id})"
                            class="inline-flex items-center gap-1 px-3 py-1 text-xs text-red-600 border border-red-200 rounded-lg hover:bg-red-50">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-3">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full bg-gray-900"
                                style="width: ${match}%"></div>
                        </div>
                    </div>

                    <!-- DETAILS (hidden by default) -->
                    <div id="details-${index}" class="hidden mt-4 border-t pt-3 text-sm text-gray-700">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div>
                                <div class="text-xs text-gray-500">Education Match</div>
                                <div class="font-medium">${row.education || 0}%</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Experience</div>
                                <div class="font-medium">${row.experience || 0}%</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Relevance</div>
                                <div class="font-medium">${row.relevance || 0}%</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">General</div>
                                <div class="font-medium">${row.general || 0}%</div>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            });
        }

        function toggleDetails(index) {
            const el = document.getElementById(`details-${index}`);
            el.classList.toggle('hidden');
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

        function scheduleInterview(rowid, id, name) {
            const confirmSchedule = confirm('Schedule interview for ' + name + '?');
            if (!confirmSchedule) {
                return;
            }

            fetch(`/matching/schedule/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Interview scheduled successfully for ' + name, 'success');
                        window.location.href = "{{ route('appointment.index') }}";
                    } else {
                        showNotification('Failed to schedule interview for ' + name, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error scheduling interview for ' + name, 'error');
                });
        }

        function markAsPassed(button) {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-applicant-name') || 'this candidate';
            const confirmPass = confirm('Mark ' + name + ' as passed?');
            if (!confirmPass) {
                return;
            }

            fetch(`/matching/pass/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Candidate marked as passed successfully', 'success');
                        // Reload the resumes for this job to show updated status
                        const currentJobId = selectedJob.value;
                        loadJobResumes(currentJobId);
                    } else {
                        showNotification('Failed to mark candidate as passed', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error marking candidate as passed', 'error');
                });
        }
    </script>
@endsection
