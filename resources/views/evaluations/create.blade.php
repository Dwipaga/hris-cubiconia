@extends('layouts.auth')
@section('title', 'Create Evaluation')

@push('styles')
<style>
    .score-slider-container {
        background: #f8f9fc;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid #4e73df;
    }
    
    .score-display {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .score-value {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .score-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .slider-controls {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .btn-volume {
        background: #4e73df;
        color: white;
        border: none;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .btn-volume:hover {
        background: #2e59d9;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        color: white;
    }
    
    .btn-volume:active {
        transform: translateY(0);
    }
    
    .score-slider {
        flex: 1;
        height: 8px;
        border-radius: 4px;
        background: #e3e6f0;
        outline: none;
        -webkit-appearance: none;
        appearance: none;
    }
    
    .score-slider::-webkit-slider-thumb {
        appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #4e73df;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }
    
    .score-slider::-webkit-slider-thumb:hover {
        background: #2e59d9;
        transform: scale(1.1);
    }
    
    .score-slider::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #4e73df;
        cursor: pointer;
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    .progress-bar-container {
        background: #e3e6f0;
        height: 8px;
        border-radius: 4px;
        position: relative;
        margin-top: 10px;
    }
    
    .progress-bar-fill {
        height: 100%;
        border-radius: 4px;
        background: linear-gradient(90deg, #fd7e14 0%, #ffc107 50%, #28a745 100%);
        transition: width 0.3s ease;
        position: relative;
    }
    
    .progress-markers {
        display: flex;
        justify-content: space-between;
        margin-top: 8px;
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    .weight-badge {
        background: linear-gradient(135deg, #11cdef 0%, #1171ef 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: bold;
        display: inline-block;
        margin-left: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <a href="{{ route('evaluations.index') }}" class="btn btn-secondary btn-icon-split">
            <span class="icon text-white-50">
                <i class="fas fa-arrow-left"></i>
            </span>
            <span class="text">Back to List</span>
        </a>
    </div>
    
    <!-- Flash Message -->
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
    </div>
    @endif
    
    <!-- Form Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-clipboard-list"></i> Evaluation Form for {{ $asesi->firstname }}
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('evaluations.store', $asesi->user_id) }}" method="POST">
                @csrf

                @foreach ($penilaians as $penilaian)
                    <div class="score-slider-container">
                        <div class="d-flex align-items-center mb-3">
                            <h5 class="mb-0 text-gray-800">{{ $penilaian->penilaian }}</h5>
                            <span class="weight-badge">Weight: {{ $penilaian->bobot }}</span>
                        </div>
                        
                        <!-- Score Display -->
                        <div class="score-display">
                            <div class="score-value" id="display_{{ $penilaian->id }}">50</div>
                            <div class="score-label">Current Score</div>
                        </div>
                        
                        <!-- Slider Controls -->
                        <div class="slider-controls">
                            <button type="button" class="btn btn-volume" onclick="decreaseScore({{ $penilaian->id }})">
                                <i class="fas fa-minus"></i>
                            </button>
                            
                            <input type="range" 
                                   class="score-slider" 
                                   id="slider_{{ $penilaian->id }}" 
                                   name="scores[{{ $penilaian->id }}]" 
                                   value="{{ old('scores.' . $penilaian->id, 50) }}"
                                   min="0" 
                                   max="100" 
                                   oninput="updateScore({{ $penilaian->id }}, this.value)"
                                   required>
                            
                            <button type="button" class="btn btn-volume" onclick="increaseScore({{ $penilaian->id }})">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" id="progress_{{ $penilaian->id }}" style="width: 50%"></div>
                        </div>
                        
                        <!-- Progress Markers -->
                        <div class="progress-markers">
                            <span>0</span>
                            <span>25</span>
                            <span>50</span>
                            <span>75</span>
                            <span>100</span>
                        </div>
                        
                        <!-- Hidden input for form submission -->
                        <input type="hidden" id="score_{{ $penilaian->id }}" name="scores[{{ $penilaian->id }}]" value="{{ old('scores.' . $penilaian->id, 50) }}">
                        
                        @error('scores.' . $penilaian->id)
                            <div class="text-danger mt-2">
                                <small><i class="fas fa-exclamation-circle"></i> {{ $message }}</small>
                            </div>
                        @enderror
                    </div>
                @endforeach

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Submit Evaluation
                    </button>
                    <a href="{{ route('evaluations.index') }}" class="btn btn-secondary btn-lg ml-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize sliders on page load
    document.addEventListener('DOMContentLoaded', function() {
        @foreach ($penilaians as $penilaian)
            updateScore({{ $penilaian->id }}, document.getElementById('slider_{{ $penilaian->id }}').value);
        @endforeach
    });

    function updateScore(penilaianId, value) {
        // Update display value
        document.getElementById('display_' + penilaianId).textContent = value;
        
        // Update hidden input
        document.getElementById('score_' + penilaianId).value = value;
        
        // Update slider value
        document.getElementById('slider_' + penilaianId).value = value;
        
        // Update progress bar
        const progressBar = document.getElementById('progress_' + penilaianId);
        progressBar.style.width = value + '%';
        
        // Change color based on score
        if (value < 40) {
            progressBar.style.background = 'linear-gradient(90deg, #dc3545 0%, #fd7e14 100%)';
        } else if (value < 70) {
            progressBar.style.background = 'linear-gradient(90deg, #fd7e14 0%, #ffc107 100%)';
        } else {
            progressBar.style.background = 'linear-gradient(90deg, #ffc107 0%, #28a745 100%)';
        }
    }
    
    function increaseScore(penilaianId) {
        const slider = document.getElementById('slider_' + penilaianId);
        let currentValue = parseInt(slider.value);
        
        if (currentValue < 100) {
            currentValue += 5; // Increase by 5
            if (currentValue > 100) currentValue = 100;
            updateScore(penilaianId, currentValue);
        }
    }
    
    function decreaseScore(penilaianId) {
        const slider = document.getElementById('slider_' + penilaianId);
        let currentValue = parseInt(slider.value);
        
        if (currentValue > 0) {
            currentValue -= 5; // Decrease by 5
            if (currentValue < 0) currentValue = 0;
            updateScore(penilaianId, currentValue);
        }
    }
    
    // Add keyboard support
    document.addEventListener('keydown', function(e) {
        if (e.target.classList.contains('score-slider')) {
            const penilaianId = e.target.id.split('_')[1];
            let currentValue = parseInt(e.target.value);
            
            if (e.key === 'ArrowUp' || e.key === 'ArrowRight') {
                e.preventDefault();
                if (currentValue < 100) {
                    updateScore(penilaianId, currentValue + 1);
                }
            } else if (e.key === 'ArrowDown' || e.key === 'ArrowLeft') {
                e.preventDefault();
                if (currentValue > 0) {
                    updateScore(penilaianId, currentValue - 1);
                }
            }
        }
    });
    
    // Add smooth animations
    document.querySelectorAll('.score-slider').forEach(slider => {
        slider.addEventListener('input', function() {
            this.style.background = `linear-gradient(to right, #4e73df 0%, #4e73df ${this.value}%, #e3e6f0 ${this.value}%, #e3e6f0 100%)`;
        });
    });
</script>
@endpush