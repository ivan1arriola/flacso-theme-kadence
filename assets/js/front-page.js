/* Parallax suave para la foto del hero; desactivado en móviles y para movimiento reducido. */
(() => {
  const hero = document.querySelector('.flacso-home .hero');
  if (!hero || !window.matchMedia || !window.requestAnimationFrame) return;

  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const desktop = window.matchMedia('(min-width: 761px)');
  let scheduled = false;

  const update = () => {
    scheduled = false;
    if (motion.matches || !desktop.matches) {
      hero.style.removeProperty('--hero-parallax');
      return;
    }
    const rect = hero.getBoundingClientRect();
    if (rect.bottom < 0 || rect.top > window.innerHeight) return;
    const distance = window.innerHeight / 2 - (rect.top + rect.height / 2);
    const shift = Math.max(-28, Math.min(28, distance * 0.12));
    hero.style.setProperty('--hero-parallax', shift.toFixed(1) + 'px');
  };

  const schedule = () => {
    if (!scheduled) {
      scheduled = true;
      window.requestAnimationFrame(update);
    }
  };
  window.addEventListener('scroll', schedule, { passive: true });
  window.addEventListener('resize', schedule, { passive: true });
  motion.addEventListener?.('change', schedule);
  desktop.addEventListener?.('change', schedule);
  schedule();
})();
