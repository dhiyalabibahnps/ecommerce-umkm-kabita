"You are a Senior Frontend Developer building a simple e-commerce web for your younger sibling's thesis. Write code so simple and clean a beginner can read it. Follow .github/copilot-instructions.md strictly."

---
## S1 • BIG-4 REUSABLES (run ONCE, first)
Create these beginner-friendly reusable components:
1. components/layout/AppHeader.vue (logo, menu Beranda/Produk/Tentang, cart icon, Login/Daftar)
2. components/layout/AppFooter.vue (copyright, kontak, link)
3. components/layout/AppSidebar.vue (wrap PrimeVue Drawer; menus by role buyer/seller/admin)
4. components/ui/BaseDialog.vue (wrap PrimeVue Dialog; props: modelValue, title, width; emit close)
5. utils/format.ts (formatRupiah, formatTanggal)
Then show how a view composes them.

## S2 • FIGMA → PAGE
@figma frame "[SPECIFIC_FRAME_NAME]" that includes the necessary design elements for the page. Build src/views/[Name]View.vue.
Compose Big-4; feature parts → components/{feature}/; mock via Pinia; UI Indonesian.
Output only new/changed files.

## S3 • TYPES + STORE
Create src/types/[module].ts + src/stores/[module].ts for "[MODULE]".
Interfaces match ERD exactly; 5–6 realistic UMKM mock rows; getters + actions; commented `// TODO(api):` axios lines.

## S4 • FIX ERROR (most efficient token)
Error: ```[PASTE ERROR]``` File: #file:[path]
MINIMAL fix only (diff style), no full rewrite. Cause in 1–2 sentences (Indonesian).

## S5 • REFACTOR TO PRIMEVUE
Active file uses raw HTML/custom CSS. Replace with PrimeVue v4 + PrimeFlex; logic & Indonesian text unchanged.

---