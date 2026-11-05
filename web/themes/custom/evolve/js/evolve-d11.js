/**
 * @file
 * Replacements for D7 inline scripts (shortcode pie graphs).
 */
(function ($, Drupal, once) {
  Drupal.behaviors.evolvePiegraph = {
    attach(context) {
      once('evolve-piegraph', 'canvas.percent_loader[data-percent]', context).forEach((canvas) => {
        const ctx = canvas.getContext('2d');
        const percent = parseInt(canvas.dataset.percent, 10);
        const color = canvas.dataset.color || '#FFF';
        const radius = canvas.width / 2 - 12;
        const x = canvas.width / 2;
        const y = canvas.height / 2;
        const start = 1.5 * Math.PI;
        ctx.lineWidth = 5;
        ctx.strokeStyle = 'rgba(255,255,255,0.5)';
        ctx.beginPath();
        ctx.arc(x, y, radius, start, 2 * Math.PI + start, false);
        ctx.stroke();
        ctx.fillStyle = '#ffffff';
        ctx.font = "17px 'raleway'";
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(percent + '%', radius + 12, radius + 12);
        ctx.strokeStyle = color;
        ctx.beginPath();
        ctx.arc(x, y, radius, start, (percent / 100) * 2 * Math.PI + start, false);
        ctx.stroke();
      });
    },
  };
})(jQuery, Drupal, once);
