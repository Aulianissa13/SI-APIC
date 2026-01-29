# TODO: Fix Leave Request System for Admin Input

## Task Description
Fix the leave request system so that when a leave request is submitted by admin, the remaining leave is automatically deducted even if the status is "waiting", and only if rejected, the leave is restored.

## Analysis
- The system was deducting leave on admin input, but storing inconsistent snapshots in pengajuan_cuti (reduced balances instead of original).
- This caused discrepancies in displaying "Sisa Tahunan" in validasi_cuti.php.
- User-side input (proses_cuti.php) stores original balances as snapshots, which is correct.
- Rejection logic in proses_validasi.php correctly restores deducted leave.

## Plan
- [x] Modify input_cuti.php to deduct leave from users table first, then insert into pengajuan_cuti with original balances as snapshots.
- [x] Add error handling: if insert fails after update, restore the balances.
- [x] Ensure consistency with user-side logic.

## Implementation
- [x] Edited pages/admin/input_cuti.php to swap order: update users table first, then insert with original balances.
- [x] Added rollback logic if insert fails.

## Testing
- [ ] Test admin input: verify leave is deducted immediately and snapshots show original balances.
- [ ] Test rejection: verify leave is restored correctly.
- [ ] Test approval: verify no additional changes (leave already deducted).
- [ ] Check validasi_cuti.php display: "Sisa Tahunan" should show balance at request time.

## Followup
- [ ] If issues found during testing, adjust logic accordingly.
- [ ] Ensure no impact on user-side submissions.
