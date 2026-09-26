# JobCapturePro Paid Funnel

This is a production-ready static funnel build (HTML/CSS/vanilla JS) designed for cold paid traffic.

## What is included
- High-conversion hero and LeadsForward authority strip
- Verified JCP public case-study metrics (Triadelphia, WV and Monroe, MI)
- 4-step Proof Waste Assessment with real calculations
- Personalized result panel
- Fully interactive six-step "See What One Job Becomes" product demo
- Existing-workflow/integration objection handling
- Real customer review copy from the current JCP website
- FAQ / ranking-claim guardrails
- Trial CTAs that route to the existing JobCapturePro app
- UTM pass-through on CTA links
- Responsive mobile/tablet/desktop behavior
- No external JS or CSS dependencies

## Launch
Upload this folder to any static host (Cloudflare Pages, Netlify, Vercel static, S3/CloudFront, WordPress static page proxy, etc.) and point the paid-ad landing-page URL to `index.html`.

For a local preview:

```bash
python3 -m http.server 8080
```

Then open http://localhost:8080

## Important production note
The existing JobCapturePro public site generates a session-based onboarding URL when visitors click "Start Free Trial." This static build currently routes trial CTAs to `https://app.jobcapturepro.com/` so it does not hard-code an expiring session ID. If the JCP app has a stable public onboarding/signup route, replace that URL in `index.html` and `app.js` before launch.

## Case study / naming
The metrics used in the proof section match the current public JobCapturePro case-study figures:
- Triadelphia foundation repair: 0% → 90%
- Triadelphia basement waterproofing: 0% → 100% (#1–#3 across grid)
- Monroe foundation repair: 0% → 84%
- Monroe basement waterproofing: 0% → 96% (#1–#3 across most grid)
- Approx. 12 weeks, March → June

The page labels this as the Acculevel case study based on the project direction. Confirm public name/logo usage rights before publishing if the public site intentionally anonymizes the company.
