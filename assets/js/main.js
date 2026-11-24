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

        const submissionTypeInputs = document.querySelectorAll('input[name="submission_type"]');
        const suggestionFields = document.querySelector('.suggestion-fields');
        const complaintFields = document.querySelector('.complaint-fields');

        function updateSubmissionFields() {
            const selected = document.querySelector('input[name="submission_type"]:checked');
            if (!selected || !suggestionFields || !complaintFields) return;
            const isSuggestion = selected.value === 'suggestion';
            suggestionFields.hidden = !isSuggestion;
            complaintFields.hidden = isSuggestion;
        }

        submissionTypeInputs.forEach(function (input) {
            input.addEventListener('change', updateSubmissionFields);
        });
        updateSubmissionFields();
    });
});
