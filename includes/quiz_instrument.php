<?php
/**
 * Quiz score instrumentation.
 *
 * The Quiz Master quiz files come in many different HTML formats. To be
 * "takeable" in our app, a quiz must report its final score to the parent
 * page via window.parent.postMessage(...). This file injects a small,
 * self-contained shim that does exactly that, so heterogeneous quiz files
 * work without hand-editing each one.
 *
 * The shim reports a score two ways:
 *   1. Explicit contract  — a quiz may call QuizMaster.report(correct, total).
 *   2. Auto-capture        — otherwise the shim watches the page for a final
 *      "X / Y" (or "You scored N%") shown in an element whose id/class mentions
 *      "score"/"result"/"grade", and reports that.
 *
 * Both qm_instrument_html() (used on upload) and tools/instrument_quizzes.php
 * (the offline batch reformatter) share this one source of truth.
 */

const QM_SENTINEL = 'quizmaster:instrumented';

/** The inline <script> shim injected into a quiz file. */
function qm_score_shim(): string
{
    // NOWDOC (single-quoted) so PHP does NOT interpret the JS $ / ${}.
    return <<<'HTML'
<!-- quizmaster:instrumented -->
<script>
(function () {
  if (window.__qmInstalled) return;
  window.__qmInstalled = true;

  var last = null;
  function post(correct, total, source) {
    correct = Number(correct); total = Number(total);
    if (!(total > 0) || correct < 0 || correct > total) return;
    var key = correct + '/' + total + '/' + source;
    if (key === last) return;
    last = key;
    try {
      window.parent.postMessage({
        quizTitle: (document.title || 'Quiz').trim(),
        score: Math.round((correct / total) * 100),
        correctAnswers: correct,
        totalQuestions: total,
        source: source
      }, '*');
    } catch (e) {}
  }

  // 1) Explicit contract for quiz authors who want a precise report.
  window.QuizMaster = window.QuizMaster || {};
  window.QuizMaster.report = function (c, t) { post(c, t, 'explicit'); };

  // 2) Auto-capture: find a visible "final score" element and read X / Y.
  var SCORE_RE = /(\d+)\s*\/\s*(\d+)/;
  function isScoreEl(el) {
    var s = ((el.id || '') + ' ' +
             (typeof el.className === 'string' ? el.className : '')).toLowerCase();
    if (!s.trim()) return false;
    if (/progress|timer|current|counter|question|qnum|streak|level|remaining/.test(s)) return false;
    return /score|result|grade|final/.test(s);
  }
  function visible(el) {
    return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
  }
  function scan() {
    var els = document.querySelectorAll('[id],[class]'), best = null;
    for (var i = 0; i < els.length; i++) {
      var el = els[i];
      if (!isScoreEl(el) || !visible(el)) continue;
      var m = (el.textContent || '').match(SCORE_RE);
      if (m) {
        var c = +m[1], t = +m[2];
        if (t >= 2 && t <= 200 && c <= t) best = { c: c, t: t };
      }
    }
    if (best) post(best.c, best.t, 'auto');
  }

  var pending;
  function schedule() { clearTimeout(pending); pending = setTimeout(scan, 250); }
  function start() {
    try {
      new MutationObserver(schedule).observe(document.body, {
        subtree: true, childList: true, characterData: true,
        attributes: true, attributeFilter: ['class', 'style']
      });
    } catch (e) {}
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
</script>
HTML;
}

/** True if the quiz already reports a score (native postMessage or our shim). */
function qm_already_reports(string $html): bool
{
    return strpos($html, QM_SENTINEL) !== false
        || strpos($html, 'parent.postMessage') !== false;
}

/**
 * Heuristic: does this quiz render a final score we can auto-capture?
 * (A score-ish element id/class + an on-screen "X / Y" or "scored N%".)
 */
function qm_has_score_element(string $html): bool
{
    $hasEl = preg_match('/(id|class)\s*=\s*["\'][^"\']*(score|result|grade|final)/i', $html);
    $hasRender = preg_match('/\d+\s*\/\s*\d+/', $html)          // literal "8 / 10"
        || preg_match('/\/\s*\$\{/', $html)                     // template `${score} / ${total}`
        || preg_match('/scored[^\d]*\d+\s*%/i', $html)          // "You scored 80%"
        || preg_match('/\b(questions\.length|quizData\.length|totalQuestions|QUIZ_COUNT)\b/', $html);
    return $hasEl && $hasRender;
}

/**
 * Inject the shim before </body> if the quiz doesn't already report a score.
 * Returns [newHtml, status, note] where status is
 * 'instrumented' | 'skipped', and note is 'auto-capture' | 'needs-review' |
 * 'already reports a score'.
 */
function qm_instrument_html(string $html): array
{
    if (qm_already_reports($html)) {
        return [$html, 'skipped', 'already reports a score'];
    }

    $note = qm_has_score_element($html) ? 'auto-capture' : 'needs-review';
    $shim = qm_score_shim();

    if (preg_match('/<\/body>/i', $html)) {
        $html = preg_replace('/<\/body>/i', $shim . "\n</body>", $html, 1);
    } else {
        $html .= "\n" . $shim . "\n";
    }

    return [$html, 'instrumented', $note];
}
