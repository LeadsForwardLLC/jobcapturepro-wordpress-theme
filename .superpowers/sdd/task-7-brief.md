### Task 7: End-to-end QA + push

**Files:** none (verification)

- [ ] **Step 1: Front-end checklist on `/` (hard-refresh)**

- [ ] Block appears after proof flow, before benefits  
- [ ] Peter Bonk featured by default  
- [ ] Three others in slider  
- [ ] Stars visible  
- [ ] Desktop + mobile layouts OK  
- [ ] Autoplay / pause / promote work  
- [ ] No console errors  
- [ ] No theme/Fluent conflicts on this section  

- [ ] **Step 2: Push branch commits to `origin`**

```bash
git push origin HEAD
```

- [ ] **Step 3: Confirm deploy / live homepage**

After SiteGround deploy, verify production homepage shows the section (cache-bust if needed).

---

## Spec coverage check

| Spec requirement | Task |
|------------------|------|
| Placement after `proof_flow` | 5 |
| Featured + slider | 2, 3, 4 |
| Peter Bonk default | 1 |
| Props / featured_key editable | 1, 6 |
| Seed from sales-tool reviews | 1, 5 |
| Homepage visual language | 3 |
| Autoplay + reduced motion | 3, 4 |
| Saved home upgrade | 5 |

## Placeholder scan

No TBD/TODO steps. Commands and code sketches are concrete; adjust only if a mirrored helper name differs slightly in `schema.php` (follow existing upgrade hook pattern).
