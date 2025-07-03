<div class="stepper-wrapper">
    <div class="stepper-inner d-flex justify-content-between">
        @foreach($steps as $index => $step)
            <div class="step @if($index === 0) active @endif" data-step="{{ $index + 1 }}">
                <div class="step-circle">{{ $index + 1 }}</div>
                <div class="step-label d-none d-md-block">{{ $step }}</div>
            </div>
        @endforeach
    </div>
</div>

<style>
.stepper-wrapper {
    margin-bottom: 20px;
    padding: 0 15px;
}
.stepper-inner {
    position: relative;
}
.step {
    text-align: center;
    position: relative;
    flex: 1;
}
.step-circle {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: #e0e0e0;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 5px;
    font-weight: bold;
}
.step.active .step-circle {
    background-color: #007bff;
}
.step.completed .step-circle {
    background-color: #28a745;
}
.step-label {
    font-size: 12px;
    color: #666;
}
@media (max-width: 768px) {
    .step-label {
        display: none;
    }
    .step-circle {
        width: 25px;
        height: 25px;
        font-size: 12px;
    }
}
</style>