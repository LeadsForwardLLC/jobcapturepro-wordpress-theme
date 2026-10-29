# Contractor-demo CRO Pass Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans (or subagent-driven-development) to implement task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix duplicate job-flow cards, constrain demo-preview to hero container width, improve headline, and tighten CRO polish on `/contractor-demo/` only.

**Architecture:** Fix PHP render + collection editor for `job_flow` benefits; update `dummy-campaign.json` + seed version bump; campaign CSS in `niche-landing.css`.

**Tech Stack:** WordPress theme PHP, front-end editor JS, campaign CSS, JSON seed.

## Global Constraints

- Scope: `/contractor-demo/` only (not live `/`, not `/home-preview/`)
- Keep current section order
- Headline: “See the whole workflow on our business”
- Seed version bump v9 → v10 with force refresh

---

### Task 1: Kill job-flow editor duplicate

**Files:**
- Modify: `inc/niche-landing/render.php` (benefits job_flow loop)
- Modify: `assets/js/pages/page-collection-editor.js` (`buildItemHtml` / rebuild)

- [ ] **Step 1:** Add `data-jcp-array-item="{index}"` on each `.jcp-job-flow__step`
- [ ] **Step 2:** Add `buildJobFlowStep` that preserves media/copy/chrome structure with editable paths
- [ ] **Step 3:** In `buildItemHtml`, if container has class `jcp-job-flow`, use `buildJobFlowStep` instead of `buildFactorCard`
- [ ] **Step 4:** Verify rebuild no longer appends `.ranking-factor-card` under job-flow

### Task 2: Campaign copy + seed v10

**Files:**
- Modify: `inc/niche-landing/dummy-campaign.json`
- Modify: `inc/niche-landing/seed.php` (version `9` → `10`)
- Modify: `inc/page-blocks/campaign-preset.php` (demo_preview layout width default if needed)

- [ ] **Step 1:** Set `demo_preview.headline` to “See the whole workflow on our business”
- [ ] **Step 2:** Ensure demo_preview layout width is default (not full/wide) in finalize
- [ ] **Step 3:** Bump `jcp_contractor_demo_seed_version` to `10` with force refresh

### Task 3: Campaign CSS CRO polish

**Files:**
- Modify: `css/pages/niche-landing.css` (`.jcp-page-campaign` rules)

- [ ] **Step 1:** Constrain demo_preview section/container to match hero gutters; kill left-bleed
- [ ] **Step 2:** Tighten section rhythm (benefits → demo → authority → problem → how_it_works)
- [ ] **Step 3:** Fix hero meta-stat wrap for “Minimal crew effort”
- [ ] **Step 4:** Strengthen final CTA / mid CTA visual hierarchy lightly

### Task 4: Verify + ship

- [ ] **Step 1:** Sanity-check key selectors / PHP lint
- [ ] **Step 2:** Commit and push to `origin` (theme always-push rule)
- [ ] **Step 3:** Confirm seed bump will reseed on next deploy/init
