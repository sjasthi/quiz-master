let quizScore = null;

window.addEventListener("message", function(event) {
    const data = event.data;

    if (!data || data.score === undefined) {
        return;
    }

    quizScore = data.score;

    const scoreBox = document.getElementById("scoreBox");
    const submitButton = document.getElementById("submitButton");

    if (scoreBox) {
        scoreBox.style.display = "block";
        scoreBox.innerHTML =
            "<strong>" + data.quizTitle + " completed!</strong><br>" +
            "Score: " + data.score + "%";
    }

    if (submitButton) {
        submitButton.disabled = false;
    }
});