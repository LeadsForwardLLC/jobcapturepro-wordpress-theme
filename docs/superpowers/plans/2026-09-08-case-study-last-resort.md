# Case Study Last-Resort CTA Implementation Plan

> **For agentic workers:** Implement task-by-task. Steps use checkbox syntax.

**Goal:** Truthful 10-spot capacity bar on `/case-study/`, tertiary demo CTA, desktop exit-intent — without interrupting demo/trial.

**Architecture:** Cohort helpers + Global Settings field; capacity markup on case-study; shared exit-intent JS/CSS on marketing pages; tertiary links in demo HTML/JS.

**Tech Stack:** WordPress PHP options, theme enqueue, vanilla JS, existing demo CSS patterns.

## Global Constraints

- Offer unchanged: 10 companies, JCP free for 90-day study
- Capacity = selected/claimed spots only (admin-editable), max 10
- Application ≠ acceptance must stay clear
- Desktop exit-intent only; never interrupt guided demo/trial
- No duplicate Meta Lead events

---

### Task 1: Cohort helpers + admin field

- [ ] Add `case_study` defaults/merge/save/UI in global settings
- [ ] Helpers: spots claimed/total/remaining + render capacity bar HTML

### Task 2: Case-study page bar

- [ ] Inject capacity bar on `/case-study/`
- [ ] Styles for bar + progress fill

### Task 3: Demo last-resort tertiary CTA

- [ ] Outcomes modal + post-demo panel tertiary link → `/case-study/`
- [ ] Track `CaseStudyCTAClicked` once

### Task 4: Desktop exit-intent

- [ ] Enqueue on home + campaign LPs only
- [ ] Mouseleave top, once/session, delay, suppress on forms/demo
- [ ] Track show + click

### Task 5: Verify + ship

- [ ] Syntax check; browser spot-check LPs → demo gate
- [ ] Commit + push
