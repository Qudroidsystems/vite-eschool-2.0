@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-primary text-white py-3 px-4 rounded">
                        <div class="page-title-left">
                            <h4 class="mb-sm-0 text-white">Computer Based Test</h4>
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('cbt.index') }}" class="text-white opacity-75">Exams</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Take Exam</li>
                            </ol>
                        </div>
                        <div class="page-title-right d-flex align-items-center text-white">
                            <div class="me-4">
                                <span class="fw-bold">Time Remaining:</span>
                                <span id="examTimer" class="fs-2 fw-bolder ms-2">00:00:00</span>
                            </div>
                            @can('Submit cbt-exam')
                                <button class="btn btn-sm btn-light" id="submitExam">Submit Exam</button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @can('Take cbt-exam')
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-0">{{ $exam->title }}</h5>
                                <p class="text-muted mb-0">{{ $exam->description }}</p>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="d-flex gap-2">
                                    <button id="fontSizeIncrease" class="btn btn-subtle-light btn-icon btn-sm">
                                        <i class="ri-add-line"></i>
                                    </button>
                                    <button id="fontSizeDecrease" class="btn btn-subtle-light btn-icon btn-sm">
                                        <i class="ri-subtract-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Questions Section -->
                                <div class="col-lg-8">
                                    <div id="questionContainer" class="mb-6">
                                        <!-- Questions will be dynamically loaded here -->
                                        <div class="current-question">
                                            <h4 class="mb-4 fs-1">Question <span id="currentQuestionNum">1</span></h4>
                                            
                                            <!-- Question content area with larger text -->
                                            <div class="question-content mb-4">
                                                <p id="questionText" class="fs-3 question-text"></p>
                                                
                                                <!-- Image container with zoom functionality -->
                                                <div id="questionImageContainer" class="text-center my-4" style="display: none;">
                                                    <div class="position-relative image-zoom-container">
                                                        <img id="questionImage" src="" alt="Question Image" class="img-fluid rounded border question-image" style="max-height: 400px;">
                                                        <div class="image-zoom-controls position-absolute bottom-0 end-0 p-2 bg-light rounded-circle m-2">
                                                            <button id="zoomIn" class="btn btn-sm btn-icon btn-light me-1">
                                                                <i class="ri-add-line fs-2"></i>
                                                            </button>
                                                            <button id="zoomOut" class="btn btn-sm btn-icon btn-light me-1">
                                                                <i class="ri-subtract-line fs-2"></i>
                                                            </button>
                                                            <button id="zoomReset" class="btn btn-sm btn-icon btn-light">
                                                                <i class="ri-refresh-line fs-2"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="options mt-4">
                                                <div class="form-check mb-4">
                                                    <input class="form-check-input question-option" type="radio" name="answer" id="option1">
                                                    <label class="form-check-label fs-4" for="option1"></label>
                                                </div>
                                                <!-- More options will be added dynamically -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pagination -->
                                    <div class="d-flex justify-content-between align-items-center">
                                        <button class="btn btn-subtle-primary btn-lg" id="prevQuestion">
                                            <i class="ri-arrow-left-line fs-2 me-1"></i> Previous
                                        </button>
                                        <div id="paginationNumbers" class="d-flex gap-2">
                                            <!-- Pagination buttons will be dynamically generated -->
                                        </div>
                                        <button class="btn btn-subtle-primary btn-lg" id="nextQuestion">
                                            Next <i class="ri-arrow-right-line fs-2 ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- End Questions Section -->

                                <!-- Question Navigator -->
                                <div class="col-lg-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="mb-4 fs-3">Question Overview</h5>
                                            <div id="questionNavigator" class="d-flex flex-wrap gap-3">
                                                <!-- Question number buttons will be generated here -->
                                            </div>
                                            <div class="mt-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <span class="bullet bg-success me-2"></span>
                                                    <span class="fs-5">Answered: <span id="answeredCount">0</span></span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <span class="bullet bg-warning me-2"></span>
                                                    <span class="fs-5">Unanswered: <span id="unansweredCount">0</span></span>
                                                </div>
                                            </div>
                                            
                                            <!-- Question Notes -->
                                            <div class="mt-8">
                                                <h6 class="fs-5 mb-3">Question Notes</h6>
                                                <textarea id="questionNotes" class="form-control" rows="4" placeholder="Add your notes for this question..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Question Navigator -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
        </div>
        <!-- End Page-content -->
    </div>
</div>

<!-- Image Modal for fullscreen view -->
<div id="imageModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Question Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Question Image Full View" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Script loaded');

    const duration = {{ $exam->duration ?? 0 }} * 60;
    console.log('Duration in minutes:', {{ $exam->duration ?? 'null' }});
    console.log('Duration in seconds:', duration);

    let timeLeft = duration;
    const timerElement = document.getElementById('examTimer');
    let timer;

    if (!timerElement) {
        console.error('Timer element not found in DOM');
    } else if (isNaN(duration) || duration <= 0) {
        console.error('Invalid duration value');
        timerElement.textContent = 'Timer Error';
    } else {
        const savedTime = localStorage.getItem('examTimeLeft');
        if (savedTime !== null) {
            timeLeft = parseInt(savedTime, 10);
            console.log('Resuming exam with time left:', timeLeft);
        }

        timer = setInterval(() => {
            const hours = Math.floor(timeLeft / 3600);
            const minutes = Math.floor((timeLeft % 3600) / 60);
            const seconds = timeLeft % 60;
            
            timerElement.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            localStorage.setItem('examTimeLeft', timeLeft);

            if (timeLeft <= 0) {
                clearInterval(timer);
                localStorage.removeItem('examTimeLeft');
                localStorage.removeItem('examAnswers');
                localStorage.removeItem('examNotes');
                submitExam(true);
            }
            timeLeft--;
        }, 1000);
    }

    const questions = @json($questions);
    console.log('Questions from backend:', questions);

    if (!questions || questions.length === 0) {
        console.error('No questions loaded from backend');
        document.getElementById('questionText').textContent = 'No questions available';
        return;
    }

    let currentQuestion = 0;
    let answers = new Array(questions.length).fill(null);
    let notes = new Array(questions.length).fill('');
    let currentFontSize = 16;
    let currentZoom = 1;
    const zoomFactor = 0.1;

    const savedAnswers = localStorage.getItem('examAnswers');
    const savedNotes = localStorage.getItem('examNotes');
    if (savedAnswers) {
        answers = JSON.parse(savedAnswers);
        console.log('Loaded saved answers:', answers);
    }
    if (savedNotes) {
        notes = JSON.parse(savedNotes);
        console.log('Loaded saved notes:', notes);
    }

    document.getElementById('fontSizeIncrease').addEventListener('click', () => {
        currentFontSize += 2;
        updateFontSize();
    });
    
    document.getElementById('fontSizeDecrease').addEventListener('click', () => {
        if (currentFontSize > 12) {
            currentFontSize -= 2;
            updateFontSize();
        }
    });
    
    function updateFontSize() {
        document.querySelector('.question-text').style.fontSize = `${currentFontSize}px`;
        document.querySelectorAll('.form-check-label').forEach(label => {
            label.style.fontSize = `${currentFontSize}px`;
        });
    }

    document.getElementById('zoomIn').addEventListener('click', () => {
        currentZoom += zoomFactor;
        updateZoom();
    });
    
    document.getElementById('zoomOut').addEventListener('click', () => {
        if (currentZoom > zoomFactor) {
            currentZoom -= zoomFactor;
            updateZoom();
        }
    });
    
    document.getElementById('zoomReset').addEventListener('click', () => {
        currentZoom = 1;
        updateZoom();
    });
    
    function updateZoom() {
        const img = document.getElementById('questionImage');
        if (img) {
            img.style.transform = `scale(${currentZoom})`;
            img.style.transformOrigin = 'center center';
            img.style.transition = 'transform 0.2s ease';
        }
    }
    
    document.getElementById('questionImage').addEventListener('click', () => {
        const img = document.getElementById('questionImage');
        if (img.src && img.src !== window.location.href) {
            document.getElementById('modalImage').src = img.src;
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            imageModal.show();
        }
    });
    
    function loadQuestion(index) {
        const question = questions[index];
        document.getElementById('currentQuestionNum').textContent = index + 1;
        document.getElementById('questionText').textContent = question.text;
        
        if (currentQuestion !== index) {
            notes[currentQuestion] = document.getElementById('questionNotes').value;
            localStorage.setItem('examNotes', JSON.stringify(notes));
        }
        
        const imageContainer = document.getElementById('questionImageContainer');
        const questionImage = document.getElementById('questionImage');
        
        console.log('Loading question', index + 1, 'Image URL:', question.image_url);
        
        if (question.image_url) {
            console.log('Attempting to load image:', question.image_url);
            imageContainer.style.display = 'block';
            questionImage.src = question.image_url;
            questionImage.alt = `Image for question ${index + 1}`;
            questionImage.onerror = () => {
                console.error('Image failed to load:', question.image_url);
                imageContainer.style.display = 'none';
            };
            questionImage.onload = () => {
                console.log('Image loaded successfully:', question.image_url);
                currentZoom = 1;
                updateZoom();
            };
        } else {
            console.log('No image for this question');
            imageContainer.style.display = 'none';
        }
        
        const optionsContainer = document.querySelector('.options');
        optionsContainer.innerHTML = '';
        question.options.forEach((option, i) => {
            const div = document.createElement('div');
            div.className = 'form-check mb-4';
            div.innerHTML = `
                <input class="form-check-input question-option" 
                       type="radio" 
                       name="answer" 
                       id="option${i}" 
                       value="${option}"
                       data-question-id="${question.id}"
                       ${answers[index] === option ? 'checked' : ''}>
                <label class="form-check-label fs-4" for="option${i}">${option}</label>
            `;
            optionsContainer.appendChild(div);
        });
        
        document.getElementById('questionNotes').value = notes[index] || '';
        currentQuestion = index;
        updateFontSize();
        updateNavigation();
    }

    function updateNavigation() {
        const navigator = document.getElementById('questionNavigator');
        navigator.innerHTML = '';
        
        let answeredCount = 0;
        questions.forEach((_, index) => {
            const btn = document.createElement('button');
            btn.className = `btn btn-lg ${answers[index] ? 'btn-success' : 'btn-warning'} ${index === currentQuestion ? 'active' : ''}`;
            btn.textContent = index + 1;
            btn.onclick = () => loadQuestion(index);
            navigator.appendChild(btn);
            if (answers[index]) answeredCount++;
        });
        
        document.getElementById('answeredCount').textContent = answeredCount;
        document.getElementById('unansweredCount').textContent = questions.length - answeredCount;
        
        const pagination = document.getElementById('paginationNumbers');
        pagination.innerHTML = '';
        questions.forEach((_, index) => {
            const btn = document.createElement('button');
            btn.className = `btn btn-sm ${index === currentQuestion ? 'btn-primary' : 'btn-light'}`;
            btn.textContent = index + 1;
            btn.onclick = () => loadQuestion(index);
            pagination.appendChild(btn);
        });
    }

    document.getElementById('prevQuestion').onclick = () => {
        if (currentQuestion > 0) loadQuestion(currentQuestion - 1);
    };
    
    document.getElementById('nextQuestion').onclick = () => {
        if (currentQuestion < questions.length - 1) loadQuestion(currentQuestion + 1);
    };
    
    @if(auth()->user()->can('Submit cbt-exam'))
    document.getElementById('submitExam').onclick = () => submitExam(false);
    @endif
    
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('question-option')) {
            const selectedOption = e.target.value;
            answers[currentQuestion] = selectedOption;
            localStorage.setItem('examAnswers', JSON.stringify(answers));
            updateNavigation();
        }
    });
    
    document.getElementById('questionNotes').addEventListener('input', (e) => {
        notes[currentQuestion] = e.target.value;
        localStorage.setItem('examNotes', JSON.stringify(notes));
    });

    function submitExam(isAutoSubmit = false) {
    notes[currentQuestion] = document.getElementById('questionNotes').value;
    localStorage.setItem('examNotes', JSON.stringify(notes));
    
    const submissionData = {
        attempt_id: {{ $attempt->id }},
        exam_id: {{ $exam->id }},
        answers: questions.map((q, index) => ({
            question_id: q.id, // Explicitly use the integer ID
            answer: answers[index],
            notes: notes[index] || null
        }))
    };

    console.log('Submitting exam data:', submissionData);

    fetch('/cbt/submit', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(submissionData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        console.log('Submission response:', data);
        if (data.success) {
            localStorage.removeItem('examTimeLeft');
            localStorage.removeItem('examAnswers');
            localStorage.removeItem('examNotes');
            window.location.href = '{{ route("cbt.index") }}';
        } else {
            throw new Error(data.message || 'Unknown error');
        }
    })
    .catch(error => {
        console.error('Submission error:', error);
        if (isAutoSubmit) {
            console.log('Auto-submit failed; will retry on reconnect');
        } else {
            alert('An error occurred while submitting the exam: ' + error.message + '. Progress saved.');
        }
    });
}

    window.addEventListener('offline', () => {
        console.log('Network disconnected; exam paused');
        clearInterval(timer);
        alert('Network disconnected. Your progress is saved. The exam will resume when the network is restored.');
    });

    window.addEventListener('online', () => {
        console.log('Network reconnected');
        if (timeLeft > 0) {
            timer = setInterval(() => {
                const hours = Math.floor(timeLeft / 3600);
                const minutes = Math.floor((timeLeft % 3600) / 60);
                const seconds = timeLeft % 60;
                
                timerElement.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                localStorage.setItem('examTimeLeft', timeLeft);

                if (timeLeft <= 0) {
                    clearInterval(timer);
                    localStorage.removeItem('examTimeLeft');
                    localStorage.removeItem('examAnswers');
                    localStorage.removeItem('examNotes');
                    submitExam(true);
                }
                timeLeft--;
            }, 1000);
            alert('Network restored. Exam resumed.');
        } else {
            submitExam(true);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            if (currentQuestion > 0) loadQuestion(currentQuestion - 1);
        }
        else if (e.key === 'ArrowRight') {
            if (currentQuestion < questions.length - 1) loadQuestion(currentQuestion + 1);
        }
        else if (/^[1-9]$/.test(e.key)) {
            const optionIndex = parseInt(e.key) - 1;
            const options = document.querySelectorAll('.question-option');
            if (optionIndex < options.length) {
                options[optionIndex].checked = true;
                answers[currentQuestion] = options[optionIndex].value;
                localStorage.setItem('examAnswers', JSON.stringify(answers));
                updateNavigation();
            }
        }
    });

    loadQuestion(0);
});
</script>

@endsection