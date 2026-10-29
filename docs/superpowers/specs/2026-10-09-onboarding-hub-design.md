# JobCapturePro Onboarding Hub Design

**Date:** 2026-10-09  
**Status:** Approved  
**Approach:** Wrap existing `/prototype/` phone shell (Approach 1)

## Goal

Customer-facing onboarding hub at `https://jobcapturepro.com/onboarding/` for live onboarding calls (screen-share), prep emails, and appointment reminders. Interactive mobile preview preserved; app download optional; CRM/photo workflows respected.

## Non-goals

- Do not alter `app.jobcapturepro.com/onboarding` (app signup).
- Do not change auth, Stripe, Firebase, CRM integrations, or Check-In processing.
- Do not rebuild the phone simulator.
- Do not add primary site nav entry.

## Routing

| URL | Behavior |
|-----|----------|
| `/onboarding/` | New template: minimal chrome + hub sections + existing `#jcp-app` |
| `/prototype/` | Permanent 301 → `/onboarding/` |
| `/wp-plugin-prototype/` | Unchanged |
| `app.jobcapturepro.com/onboarding` | Unchanged (different host) |

## Chrome

- Header: small JCP logo → homepage only (no menus/CTAs).
- Footer: © JobCapturePro, Privacy Policy, Support (thin; not full marketing footer).
- Meta: `noindex, follow`. Title: `Getting Started | JobCapturePro Onboarding`.

## Layout

1. Welcome hero (eyebrow / H1 / support copy).
2. Desktop: phone preview (centerpiece) + iOS/Android download cards side-by-side so phone + both QRs fit at normal zoom during screen-share.
3. Mobile: stack; prioritize official store badges; hide QR codes.
4. CRM/photo alternative (reassuring; Housecall Pro / CompanyCam / camera roll).
5. Short prep checklist (3 items): photo source, business logins, recent job photos.
6. Closing reassurance.

## Preview

Reuse existing `#jcp-app` / `JCP_IS_PROTOTYPE` / phone-shell enqueue path. Add heading + “INTERACTIVE APP PREVIEW” label only. Preserve gestures and transitions. Scale the phone for viewport fit without changing simulator internals.

## Downloads

- iOS: `https://apps.apple.com/us/app/jobcapturepro/id6636248590`
- Android: `https://play.google.com/store/apps/details?id=com.jobcapturepro.mobile&pcampaignid=web_share`
- Static QR assets in theme (`assets/brand/qr/*`; no third-party runtime QR APIs).
- Official App Store / Google Play badge SVGs (`assets/badges/*`); full card clickable.

## Analytics

Reuse PostHog / dataLayer:

- `onboarding_page_viewed`
- `onboarding_app_store_clicked` `{ platform: 'ios' | 'android' }` (button clicks only)
- `onboarding_preview_interacted` (once per session)

Do not claim QR scan tracking unless a real scan mechanism exists.

## Product messaging

Either capture path is valid: JCP app **or** existing CRM/photo workflow. Never imply the app is mandatory. Download section is optional (“if you’ll be creating Check-Ins with the app”).

## Independence from app signup

- Marketing hub: `jobcapturepro.com/onboarding/`
- App signup: `app.jobcapturepro.com/onboarding`
- Niche CTA “Start Free Trial” tracking applies only to the app host.
- Shared `window.JCP_ONBOARDING` / handoff scripts are not required for this hub page.

## Implementation notes

- Template: `page-onboarding.php`
- Styles: `css/pages/onboarding.css`
- Analytics: `assets/js/pages/onboarding.js`
- Detection: `jcp_core_get_page_detection()['is_onboarding']`
- Path force + `/prototype/` 301: `inc/template-routes.php`
