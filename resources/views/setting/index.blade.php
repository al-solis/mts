@extends('dashboard')
@section('content')
    <div class="p-6 space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">System Settings</h1>
                <p class="text-sm text-gray-500">
                    Configure AI matching criteria.
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
        {{-- Settings Form --}}
        <div class="bg-white rounded-xl shadow-xs p-4 sm:p-7">
            <form method="POST" action="{{ route('setting.update', $settings->id) }}">
                @csrf
                @method('PUT')

                <h1 class="text-lg font-semibold text-gray-900 mb-4">AI Matching Configuration</h1>
                <label for="ai_matching_criteria" class="block text-sm font-semibold text-gray-900 dark:text-white">Minimum
                    Match Percentage per
                    Qualification</label>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                    <div>
                        <input type="number" id="ai_matching_criteria" name="ai_matching_criteria" min="50"
                            max="100"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                            value="{{ old('ai_matching_criteria', $settings->minimum_match_percentage) }}">
                    </div>
                </div>
                <label for="ai_matching_enabled"
                    class="block mt-2 text-sm font-medium text-gray-900 dark:text-white">Current Threshold:
                    {{ $settings->minimum_match_percentage }}%</label>

                <label class="block mt-2 text-sm font-medium text-gray-900 dark:text-white">Applicants with a match score of
                    <span
                        id="ai_matching_score">{{ old('ai_matching_criteria', $settings->minimum_match_percentage) }}</span>
                    <span> % or higher will be marked as qualified candidates</span>
                </label>

                <div class="mt-2 border rounded-md p-3 bg-blue-50 border-blue-400 text-blue-700">
                    <h1 class="font-semibold mb-4">MATCH SCORE BREAKDOWN:</h1>
                    {{-- <i class="bi bi-exclamation-triangle-fill inline-block mr-2"></i> --}}
                    <ul class="list-disc list-inside text-sm mb-2 space-y-2">
                        <li class="flex items-center gap-2">
                            <span class="whitespace-nowrap">Education Match:</span>
                            <input type="number" name="education" id="education"
                                value="{{ old('education', $settings->education) }}"
                                class="w-20 bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 p-1.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                                placeholder="e.g. 20" required>
                            <span class="whitespace-nowrap">% weight</span>
                        </li>

                        <li class="flex items-center gap-2">
                            <span class="whitespace-nowrap">Years of Experience:</span>
                            <input type="number" name="years_of_experience" id="years_of_experience"
                                value="{{ old('years_of_experience', $settings->years_of_experience) }}"
                                class="w-20 bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 p-1.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                                placeholder="e.g. 25" required>
                            <span class="whitespace-nowrap">% weight</span>
                        </li>

                        <li class="flex items-center gap-2">
                            <span class="whitespace-nowrap">Work Experience Relevance:</span>
                            <input type="number" name="work_experience_relevance" id="work_experience_relevance"
                                value="{{ old('work_experience_relevance', $settings->work_experience_relevance) }}"
                                class="w-20 bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 p-1.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                                placeholder="e.g. 25" required>
                            <span class="whitespace-nowrap">% weight</span>
                        </li>

                        <li class="flex items-center gap-2">
                            <span class="whitespace-nowrap">Skills Match:</span>
                            <input type="number" name="skills_match" id="skills_match"
                                value="{{ old('skills_match', $settings->skills) }}"
                                class="w-20 bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 p-1.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                                placeholder="e.g. 25" required>
                            <span class="whitespace-nowrap">% weight</span>
                        </li>

                        <li class="flex items-center gap-2">
                            <span class="whitespace-nowrap">Related Certifications:</span>
                            <input type="number" name="related_certifications" id="related_certifications"
                                value="{{ old('related_certifications', $settings->certifications) }}"
                                class="w-20 bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 p-1.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                                placeholder="e.g. 25" required>
                            <span class="whitespace-nowrap">% weight</span>
                        </li>

                        <li class="flex items-center gap-2">
                            <span class="whitespace-nowrap">General Qualifications:</span>
                            <input type="number" name="general_qualifications" id="general_qualifications"
                                value="{{ old('general_qualifications', $settings->general) }}"
                                class="w-20 bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 p-1.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                                placeholder="e.g. 25" required>
                            <span class="whitespace-nowrap">% weight</span>
                        </li>
                    </ul>
                    {{-- <span class="text-sm">Adjusting the minimum match percentage will impact how candidates are evaluated
                        against job requirements. A higher percentage may result in fewer candidates being marked as
                        qualified, while a lower percentage may increase the pool of qualified candidates. Please review
                        changes carefully before saving.</span> --}}

                </div>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-6">

                    <div class="mt-6">
                        <button type="submit"
                            class="mt-2 text-white inline-flex items-center bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-md text-xs px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                            Save Settings
                        </button>
                    </div>
                </div>
            </form>
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

        document.addEventListener('DOMContentLoaded', function() {
            const aiMatchingCriteriaInput = document.getElementById('ai_matching_criteria');
            const aiMatchingScoreSpan = document.getElementById('ai_matching_score');

            aiMatchingCriteriaInput.addEventListener('input', function() {
                aiMatchingScoreSpan.textContent = aiMatchingCriteriaInput.value;
            });

            const weightInputs = document.querySelectorAll(
                'input[name="education"], input[name="years_of_experience"], input[name="work_experience_relevance"], input[name="skills_match"], input[name="related_certifications"], input[name="general_qualifications"]'
            );

            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                let totalWeight = 0;
                weightInputs.forEach(input => {
                    totalWeight += parseFloat(input.value) || 0;
                });

                if (totalWeight !== 100) {
                    event.preventDefault();
                    alert(
                        'The total weight of all criteria must equal 100%. Please adjust the values accordingly.'
                    );
                }
            });
        });
    </script>
@endsection
