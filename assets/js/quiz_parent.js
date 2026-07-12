let latestResult = null;
let isSubmitting = false;

window.addEventListener("message", function (event) {
    const data = event.data;

    if (!data || data.score === undefined) {
        return;
    }

    latestResult = data;

    const scoreBox = document.getElementById("scoreBox");
    const submitButton = document.getElementById("submitButton");
    const submitError = document.getElementById("submitError");

    if (scoreBox) {
        scoreBox.style.display = "block";
        scoreBox.innerHTML =
            "<strong>" + data.quizTitle + " completed!</strong><br>" +
            "You scored <strong>" + data.score + "%</strong>" +
            " (" + data.correctAnswers + " of " + data.totalQuestions + " correct)." +
            "<br>Click <em>Record My Score</em> below to save this attempt.";
    }

    if (submitError) {
        submitError.style.display = "none";
    }

    if (submitButton) {
        submitButton.disabled = false;
    }
});

async function recordScore() {
    if (!latestResult || isSubmitting) {
        return;
    }

    const submitButton = document.getElementById("submitButton");
    const submitError = document.getElementById("submitError");

    isSubmitting = true;

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = "Saving...";
    }

    if (submitError) {
        submitError.style.display = "none";
    }

    // The parent page (quiz_take.php) tells us which registered quiz this is,
    // via window.QM_QUIZ. That is the source of truth for the quiz identity —
    // the score/answers come from whatever quiz ran inside the iframe.
    const quiz = window.QM_QUIZ || {};
    const payload = {
        quizTitle: quiz.title || latestResult.quizTitle,
        quizFile: quiz.file || latestResult.quizFile,
        score: latestResult.score,
        correctAnswers: latestResult.correctAnswers,
        totalQuestions: latestResult.totalQuestions,
        answers: latestResult.answers || []
    };

    try {
        const response = await fetch("../api/submit_score.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.error || "Could not save your score.");
        }

        window.location.href = "quiz_results.php?attempt=" + result.attempt_id;
    } catch (err) {
        isSubmitting = false;

        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = "Record My Score";
        }

        if (submitError) {
            submitError.style.display = "block";
            submitError.textContent = err.message;
        }
    }
}
