document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.querySelector('.menu-toggle');
    const navigation = document.querySelector('.main-nav');

    if (menuToggle && navigation) {
        menuToggle.addEventListener('click', function () {
            const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', String(!isOpen));
            navigation.classList.toggle('is-open', !isOpen);
        });
    }

    document.querySelectorAll('.save-button').forEach(function (button) {
        button.addEventListener('click', function () {
            const saved = button.classList.toggle('is-saved');
            button.textContent = saved ? '♥' : '♡';
            button.setAttribute('aria-pressed', String(saved));
        });

    });

    const submissionTypeInputs = document.querySelectorAll('input[name="submission_type"]');
    const suggestionFields = document.querySelector('.suggestion-fields');
    const messageLabel = document.querySelector('.submission-message-label');
    const messageField = document.querySelector('textarea[name="description"]');
    const submitLabel = document.querySelector('.submission-button-label');

    function updateSubmissionFields() {
        const selected = document.querySelector('input[name="submission_type"]:checked');
        if (!selected || !suggestionFields || !messageLabel || !messageField || !submitLabel) return;
        const isSuggestion = selected.value === 'suggestion';
        suggestionFields.hidden = !isSuggestion;
        messageLabel.textContent = isSuggestion ? 'Tell us about this opportunity' : 'Your complaint';
        messageField.placeholder = isSuggestion ? 'Explain why this benefit should be added.' : 'Describe the issue or concern you would like us to review.';
        submitLabel.textContent = isSuggestion ? 'Send suggestion' : 'Submit complaint';
    }

    submissionTypeInputs.forEach(function (input) {
        input.addEventListener('change', updateSubmissionFields);
    });
    updateSubmissionFields();
});
