# Proof Gap mobile polish (conversion)

Date: 2026-09-29  
Scope: small polish only — no funnel restructure.

## Goals
- Let the proof-gap result land before the email ask
- Stop truncated progress labels on ~390px
- Stop sticky CTA from covering last content lines

## Changes
1. Remove `scrollIntoView` on `result_email` enter (keep stage scrollTop = 0).
2. Shorten progress labels to Work / Seen / Plan.
3. Bump bottom clearance on welcome, result_email, and trial_bridge so last content clears the sticky CTA.

## Non-goals
- Copy rewrites, new sections, layout redesign.
