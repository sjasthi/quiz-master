/* Parent-side logic for the quiz page (student/quiz_take.php).
 *
 * The quiz itself lives in an <iframe> (quizzes/python/quiz1.html). When the
 * student finishes, the iframe posts its score up to this page with
 * window.parent.postMessage(...). We listen for that here, reveal the score,
 * and enable the Submit button so the student can record the attempt.
 *
 * Submitting saves the attempt to localStorage for this demo (no database
 * yet) using the same keys the dashboard and results pages read.
 */

let latestResult = null;

window.addEventListener("message", function (event) {
    const data = event.data;

    // Ignore unrelated messages (browser extensions, etc.).
    if (!data || data.score === undefined) {
        return;
    }

    latestResult = data;

    const scoreBox = document.getElementById("scoreBox");
    const submitButton = document.getElementById("submitButton");

    if (scoreBox) {
        scoreBox.style.display = "block";
        scoreBox.innerHTML =
            "<strong>" + data.quizTitle + " completed!</strong><br>" +
            "You scored <strong>" + data.score + "%</strong>" +
            " (" + data.correctAnswers + " of " + data.totalQuestions + " correct)." +
            "<br>Click <em>Record My Score</em> below to save this attempt.";
    }

    if (submitButton) {
        submitButton.disabled = false;
    }
});

function recordScore() {
    if (!latestResult) {
        return;
    }

    const attempts =
        JSON.parse(localStorage.getItem("pythonQuizAttempts")) || [];

    attempts.push({
        quiz: latestResult.quizTitle,
        attempt: attempts.length + 1,
        score: latestResult.score,
        correct: latestResult.correctAnswers,
        total: latestResult.totalQuestions,
        status: "Submitted",
        date: new Date().toLocaleDateString()
    });

    localStorage.setItem("pythonQuizAttempts", JSON.stringify(attempts));
    localStorage.setItem("pythonQuizScore", latestResult.score);
    localStorage.setItem("pythonQuizCorrect", latestResult.correctAnswers);
    localStorage.setItem("pythonQuizTotal", latestResult.totalQuestions);

    window.location.href = "quiz_results.php";
}
