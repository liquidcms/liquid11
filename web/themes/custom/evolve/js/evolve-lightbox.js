/**
 * @file
 * Replaces D7 lightbox2: links with rel="lightbox[group]" open a GLightbox
 * gallery of every image in the same group.
 */
(function (Drupal, once) {
  Drupal.behaviors.evolveLightbox = {
    attach(context) {
      once('evolve-lightbox', 'a[rel^="lightbox"]', context).forEach((link) => {
        link.addEventListener('click', (event) => {
          event.preventDefault();
          const rel = link.getAttribute('rel');
          // bxSlider clones slides; use each image once, in page order.
          const hrefs = [];
          document.querySelectorAll('a[rel]').forEach((a) => {
            if (a.getAttribute('rel') === rel && !hrefs.includes(a.href)) {
              hrefs.push(a.href);
            }
          });
          const gallery = GLightbox({
            elements: hrefs.map((href) => ({ href, type: 'image' })),
            loop: true,
          });
          gallery.openAt(Math.max(0, hrefs.indexOf(link.href)));
        });
      });
    },
  };
})(Drupal, once);
