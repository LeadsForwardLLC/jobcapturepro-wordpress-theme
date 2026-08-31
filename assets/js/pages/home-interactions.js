(() => {
  function buildWavePoints(x1, x2, centerY, amplitude, waves) {
    const points = [];
    const steps = 32;
    for (let i = 0; i <= steps; i++) {
      const t = i / steps;
      const x = x1 + t * (x2 - x1);
      const y = centerY + amplitude * Math.sin(t * Math.PI * 2 * waves);
      points.push({ x, y });
    }
    return points;
  }

  function buildContinuousWavePath(centerXs, centerY, amplitude, waves, padding) {
    if (centerXs.length < 2) return '';
    const allPoints = [];
    const startX = centerXs[0] - padding;
    const endX = centerXs[centerXs.length - 1] + padding;
    const segStarts = [startX, ...centerXs];
    const segEnds = [...centerXs, endX];
    for (let i = 0; i < segStarts.length; i++) {
      const pts = buildWavePoints(segStarts[i], segEnds[i], centerY, amplitude, waves);
      if (i === 0) {
        allPoints.push(...pts);
      } else {
        allPoints.push(...pts.slice(1));
      }
    }
    return 'M ' + allPoints.map((p) => p.x + ' ' + p.y).join(' L ');
  }

  function initProofFlowLines(rootEl) {
    const flow = rootEl && rootEl.querySelector('.proof-flow');
    const linesEl = flow && flow.querySelector('.proof-flow-lines');
    if (!flow || !linesEl) return;

    const items = flow.querySelectorAll('.proof-flow-item');
    if (items.length < 2) return;

    const flowRect = flow.getBoundingClientRect();
    const iconCenterXs = [];
    let iconCenterY = 32;
    items.forEach((item) => {
      const iconEl = item.querySelector('.factor-icon-wrapper');
      if (iconEl) {
        const r = iconEl.getBoundingClientRect();
        iconCenterXs.push(r.left - flowRect.left + r.width / 2);
        if (iconCenterXs.length === 1) {
          iconCenterY = r.top - flowRect.top + r.height / 2;
        }
      }
    });
    if (iconCenterXs.length < 2) return;

    const w = flowRect.width;
    const h = flowRect.height;
    const amplitude = 5;
    const waves = 2;
    const padding = 24;

    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('class', 'proof-flow-waves');
    svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
    svg.setAttribute('preserveAspectRatio', 'none');
    svg.setAttribute('aria-hidden', 'true');

    const delays = [0, 0.4, 0.8];
    [0, -2, 2].forEach((dy, idx) => {
      const pathD = buildContinuousWavePath(iconCenterXs, iconCenterY + dy, amplitude, waves, padding);
      const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      path.setAttribute('class', 'proof-flow-wave');
      path.setAttribute('d', pathD);
      path.setAttribute('fill', 'none');
      path.setAttribute('stroke', 'currentColor');
      path.setAttribute('stroke-width', dy === 0 ? '2' : '1.5');
      path.setAttribute('stroke-linecap', 'round');
      path.style.opacity = dy === 0 ? '1' : '0.5';
      path.style.animationDelay = delays[idx] + 's';
      svg.appendChild(path);
    });

    linesEl.innerHTML = '';
    linesEl.appendChild(svg);
  }

  function initRotatingWord(root) {
    const rotatingWordEl = root.querySelector('.jcp-hero-rotating-word');
    if (!rotatingWordEl) return;

    let words = [];
    try {
      words = JSON.parse(rotatingWordEl.getAttribute('data-words') || '[]');
    } catch (e) {
      words = [];
    }
    if (!words.length) {
      words = ['opportunities', 'visibility', 'reviews', 'calls', 'proof'];
    }

    let index = 0;
    const cycleMs = 2800;
    const fadeMs = 350;
    rotatingWordEl.style.transition = `opacity ${fadeMs}ms ease`;

    setInterval(() => {
      rotatingWordEl.style.opacity = '0';
      setTimeout(() => {
        index = (index + 1) % words.length;
        rotatingWordEl.textContent = words[index];
        rotatingWordEl.style.opacity = '1';
      }, fadeMs);
    }, cycleMs);
  }

  function initGuaranteeFaqLinks(root) {
    root.querySelectorAll('.guarantee-item[data-faq-target]').forEach((item) => {
      item.addEventListener('click', (e) => {
        e.preventDefault();
        const targetId = item.getAttribute('data-faq-target');
        const faqSection = document.getElementById('faq');
        const targetFaq = targetId ? document.getElementById(targetId) : null;
        if (faqSection) {
          faqSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
          setTimeout(() => {
            if (targetFaq && targetFaq.tagName === 'DETAILS') {
              targetFaq.open = true;
              targetFaq.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          }, 300);
        }
      });
    });
  }

  function init() {
    const root = document.querySelector('main.jcp-home');
    if (!root) return;

    initRotatingWord(root);
    initGuaranteeFaqLinks(root);

    const drawLines = () => initProofFlowLines(root);
    requestAnimationFrame(() => requestAnimationFrame(drawLines));

    let resizeTimer = null;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(drawLines, 150);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
