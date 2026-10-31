<?php http_response_code(404); exit; ?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body><title>Preventivi Dvor</title>
<meta name="claude:viewport-fit-sides" content="cover">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&display=swap">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<style>
/* Layout: editor column on the left, live A4 quote on the right (stacked on phones). */
:root {
  --bg: #f3f1ef;
  --panel: #ffffff;
  --ink: #1c1a19;
  --muted: #6b6560;
  --line: #e2ddd8;
  --field: #faf9f8;
  --accent: #c8432c;      /* the red dot in the DVOR logo */
  --accent-ink: #ffffff;
  --accent-soft: #f7e3de;
  --ok: #2f7a4a;
  --ui: "Source Sans 3", system-ui, -apple-system, "Segoe UI", sans-serif;
  --doc: "Source Serif 4", Georgia, "Times New Roman", serif;          /* quote body: a readable book serif */
  --display: "Cormorant Garamond", Garamond, Georgia, serif;           /* course names and headings, close to the DVOR logo */
}
@media (prefers-color-scheme: dark) {
  :root:not([data-theme="light"]) {
    --bg: #171514; --panel: #211f1e; --ink: #efebe7; --muted: #a59e98; --line: #38332f;
    --field: #1b1918; --accent: #e5664f; --accent-ink: #1c0d09; --accent-soft: #3a2420; --ok: #6cc28c;
    color-scheme: dark;
  }
}
:root[data-theme="dark"] {
  --bg: #171514; --panel: #211f1e; --ink: #efebe7; --muted: #a59e98; --line: #38332f;
  --field: #1b1918; --accent: #e5664f; --accent-ink: #1c0d09; --accent-soft: #3a2420; --ok: #6cc28c;
  color-scheme: dark;
}
* { box-sizing: border-box; }
body { background: var(--bg); color: var(--ink); font-family: var(--ui); font-size: 15px; }
body::before { content: ""; position: fixed; inset: 0 0 auto; height: env(safe-area-inset-top, 0px); z-index: 10; background: var(--panel); }

.topbar {
  position: sticky; top: env(safe-area-inset-top, 0px); z-index: 5;
  background: var(--panel); border-bottom: 1px solid var(--line);
  padding-left: max(16px, env(safe-area-inset-left, 0px)); padding-right: max(16px, env(safe-area-inset-right, 0px));
  padding-block: 10px;
  display: flex; flex-wrap: wrap; align-items: center; gap: 10px 14px;
}
.brand { display: flex; align-items: baseline; gap: 8px; margin-right: auto; }
.brand b { font-family: var(--display); font-size: 22px; font-weight: 700; letter-spacing: .02em; }
.brand span { color: var(--muted); font-size: 13px; }
.dot { width: 9px; height: 9px; border-radius: 50%; background: var(--accent); display: inline-block; transform: translateY(-1px); }
.tb-group { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
.who { font-size: 13px; color: var(--muted); }
.who a { color: var(--ink); font-weight: 600; }
.sync { font-size: 12px; color: var(--muted); }
.sync.err { color: var(--accent); font-weight: 600; }

button, .btn {
  font: inherit; font-weight: 600; font-size: 13px; cursor: pointer;
  border: 1px solid var(--line); background: var(--panel); color: var(--ink);
  border-radius: 7px; padding: 7px 12px; line-height: 1.2;
}
button:hover { border-color: var(--muted); }
button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible { outline: 2px solid var(--accent); outline-offset: 1px; }
button.primary { background: var(--accent); border-color: var(--accent); color: var(--accent-ink); }
button.ghost { border-color: transparent; background: transparent; color: var(--muted); padding: 5px 7px; }
button.ghost:hover { color: var(--ink); background: var(--field); }
button.danger { color: var(--accent); }
button:disabled { opacity: .5; cursor: default; }

.layout {
  display: grid; grid-template-columns: minmax(0, 470px) minmax(0, 1fr); gap: 20px;
  padding-left: max(16px, env(safe-area-inset-left, 0px)); padding-right: max(16px, env(safe-area-inset-right, 0px));
  padding-block: 18px 40px;
  max-width: 1500px; margin: 0 auto;
}
@media (max-width: 960px) { .layout { grid-template-columns: minmax(0, 1fr); } }

.editor { display: flex; flex-direction: column; gap: 14px; min-width: 0; }
.card { background: var(--panel); border: 1px solid var(--line); border-radius: 10px; padding: 14px 14px 16px; display: flex; flex-direction: column; gap: 12px; min-width: 0; }
.card h2 { margin: 0; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); font-weight: 700; display: flex; align-items: center; gap: 8px; }
.card h2 .spacer { flex: 1; }
.grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
.grid.three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.full { grid-column: 1 / -1; }
label.f { display: flex; flex-direction: column; gap: 4px; font-size: 12px; color: var(--muted); font-weight: 500; min-width: 0; }
input, select, textarea {
  font: inherit; font-size: 14px; color: var(--ink); background: var(--field);
  border: 1px solid var(--line); border-radius: 6px; padding: 7px 9px; width: 100%; min-width: 0;
}
textarea { resize: vertical; line-height: 1.4; }
input[type="number"] { font-variant-numeric: tabular-nums; }
input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--accent); }
.check { display: inline-flex; align-items: center; gap: 7px; font-size: 13px; color: var(--ink); font-weight: 500; cursor: pointer; }
.row { display: flex; gap: 8px; align-items: center; min-width: 0; }
.row > .grow { flex: 1; min-width: 0; }
.hint { font-size: 12px; color: var(--muted); }

.sect { border-top: 1px solid var(--line); padding-top: 12px; display: flex; flex-direction: column; gap: 8px; }
.sect:first-of-type { border-top: 0; padding-top: 0; }
.sect-head { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.3fr) auto; gap: 8px; align-items: center; }
.sect-head input.lbl { font-weight: 700; }
.items { display: flex; flex-direction: column; gap: 6px; }
.item { display: flex; gap: 4px; align-items: center; }
.item input { flex: 1; font-style: italic; }
.count { font-size: 11px; font-weight: 700; background: var(--accent-soft); color: var(--accent); border-radius: 99px; padding: 2px 7px; }
.sect.empty .count { background: var(--field); color: var(--muted); }

.wine-row { display: grid; grid-template-columns: minmax(0, 1fr) 64px 64px auto; gap: 6px; align-items: center; }
.wine-head { font-size: 11px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }

.totals { display: flex; flex-direction: column; gap: 4px; font-variant-numeric: tabular-nums; }
.totals div { display: flex; justify-content: space-between; gap: 10px; padding: 4px 0; border-bottom: 1px dashed var(--line); }
.totals div.strong { font-weight: 700; }
.totals div.acc { color: var(--accent); font-weight: 700; border-bottom: 0; }

.preview { min-width: 0; position: sticky; top: calc(env(safe-area-inset-top, 0px) + 70px); align-self: start; }
@media (max-width: 960px) { .preview { position: static; } }
.preview-bar { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 10px; flex-wrap: wrap; }
.preview-bar .hint b { color: var(--ink); }
.warn { color: var(--accent); font-weight: 600; font-size: 12px; }
.pages-viewport { overflow-y: auto; max-height: calc(100vh - 150px); border-radius: 8px; }
@media (max-width: 960px) { .pages-viewport { max-height: none; } }
.pages-scale { transform-origin: top left; }
.pages { display: flex; flex-direction: column; gap: 18px; width: 794px; }

.toast { position: fixed; left: 50%; bottom: calc(18px + env(safe-area-inset-bottom, 0px)); transform: translateX(-50%); background: var(--ink); color: var(--bg); padding: 9px 14px; border-radius: 8px; font-weight: 600; font-size: 13px; z-index: 20; max-width: calc(100% - 32px); }

.archive { display: flex; flex-direction: column; gap: 4px; max-height: 240px; overflow-y: auto; }
.arch-item { display: flex; align-items: center; gap: 6px; padding: 6px 8px; border-radius: 6px; border: 1px solid transparent; }
.arch-item.cur { border-color: var(--accent); background: var(--accent-soft); }
.arch-item .meta { flex: 1; min-width: 0; cursor: pointer; }
.arch-item .meta b { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.arch-item .meta span { font-size: 12px; color: var(--muted); }

/* ---------- The quote document: always printed on white, independent of theme ---------- */
.page {
  width: 794px; min-height: 1123px; background: #ffffff; color: #111111;
  font-family: var(--doc); font-size: 11.5px; line-height: 1.45; font-variant-numeric: lining-nums;
  padding: 52px 72px 50px 72px; position: relative;
  box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 6px 24px rgba(0,0,0,.08);
  display: flex; flex-direction: column;
}
.page.fixed { height: 1123px; }
/* Page frame: a black double rule like the DVOR lettering, with the logo's red dot at the corners */
.frame { position: absolute; inset: 22px; border: 1.3px solid #1c1a19; pointer-events: none; }
.frame::before { content: ""; position: absolute; inset: 4px; border: .6px solid #1c1a19; }
.frame .c { position: absolute; width: 9px; height: 9px; border-radius: 50%; background: #c8432c; box-sizing: content-box; border: 3px solid #ffffff; }
.frame .tl { top: -7.2px; left: -7.2px; } .frame .tr { top: -7.2px; right: -7.2px; }
.frame .bl { bottom: -7.2px; left: -7.2px; } .frame .br { bottom: -7.2px; right: -7.2px; }
.frame .b { position: absolute; left: 50%; bottom: -4.6px; width: 34px; height: 8px; margin-left: -17px; background: #ffffff; }
.frame .b::after { content: ""; position: absolute; left: 13px; top: 0; width: 8px; height: 8px; border-radius: 50%; background: #c8432c; }
.d-head { display: grid; grid-template-columns: 1fr 1fr; column-gap: 20px; align-items: start; }
.d-client { display: flex; flex-direction: column; align-items: center; text-align: center; }
.d-client .k2, .d-obj .k2 { display: block; font-family: var(--ui); font-weight: 600; font-size: 9px; text-transform: uppercase; letter-spacing: .1em; color: #555; margin-bottom: 4px; }
.d-brand { display: flex; flex-direction: column; align-items: center; }
.d-rule { width: 60px; height: 1px; background: #c8432c; margin: 20px auto 18px; }
.d-client b { font-family: var(--display); font-size: 17px; }
.d-client div { font-size: 10.5px; }
.d-meta { display: grid; grid-template-columns: auto auto; justify-content: center; column-gap: 14px; row-gap: 8px; align-items: baseline; padding-top: 2px; }
.d-meta .k { font-family: var(--ui); font-weight: 600; font-size: 9px; text-transform: uppercase; letter-spacing: .1em; color: #555; text-align: right; }
.d-meta .v { font-size: 12px; }
.d-logo-row { display: grid; grid-template-columns: 1fr 1fr; column-gap: 20px; align-items: center; }
.d-logo { width: 140px; margin-top: 2px; display: block; }
.d-venue { font-family: var(--display); font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .14em; font-variant-numeric: lining-nums; line-height: 1.55; margin-top: 8px; color: #333; text-align: center; white-space: nowrap; display: flex; flex-direction: column; align-items: center; }
.d-addr.d-company { margin-top: 0; display: flex; white-space: nowrap; flex-direction: column; align-items: center; text-align: center; }
.d-company b { font-family: var(--display); font-size: 15px; letter-spacing: .03em; }
.d-addr { display: grid; grid-template-columns: 130px 1fr; white-space: nowrap; font-size: 11.5px; line-height: 1.35; margin-top: 18px; }
.d-sec { display: flex; flex-direction: column; align-items: center; text-align: center; }
.d-sec .k, .d-obj .k, .d-wine .k { font-family: var(--display); font-size: 15px; line-height: 1.1; font-weight: 700; white-space: nowrap; }
.d-sub { text-align: center; color: #d22b1f; font-family: var(--ui); font-size: 9px; font-weight: 600; line-height: 1.3; white-space: pre-line; margin-top: 3px; }
.d-items { font-style: italic; margin-top: 6px; }
.d-items > div + div { margin-top: 5px; }
.leaf { width: 1em; height: 1em; vertical-align: -0.12em; margin-left: 4px; }
.check.suppl { white-space: nowrap; font-size: 12px; color: #d22b1f; font-weight: 600; }
.d-suppl { font-family: var(--ui); font-style: normal; font-size: 0.8em; font-weight: 600; color: #d22b1f; margin-left: 6px; white-space: nowrap; }
.d-holly { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 14px auto 12px; }
.d-holly::before, .d-holly::after { content: ""; width: 34px; height: 1px; background: #c8432c; }
.d-holly .holly { width: 46px; height: 16px; }
.d-xmas { text-align: center; font-family: var(--display); font-style: italic; font-size: 12px; color: #2f6b3a; margin: 10px 0 4px; }
.d-legend span + span { margin-left: 14px; }
.d-legend { text-align: center; font-family: var(--ui); font-size: 9px; color: #555; margin-top: 8px; }
.d-legend .leaf { margin: 0 2px 0 0; }
.d-pair { display: grid; grid-template-columns: 1fr 1fr; column-gap: 28px; align-items: start; }
/* Course names stand out: small caps with a thin red rule each side */
.d-sec > .k, .d-wine > .k { display: flex; align-items: center; gap: 12px; font-size: 16.5px; text-transform: uppercase; letter-spacing: .16em; }
.d-sec > .k::before, .d-sec > .k::after, .d-wine > .k::before, .d-wine > .k::after { content: ""; width: 28px; height: 1px; background: #c8432c; }
.page.compact .d-items > div + div { margin-top: 3px; }
.page.compact2 .d-items > div + div { margin-top: 2px; }
.page.compact2 .d-items { margin-top: 4px; }
.page.compact3 .d-items > div + div { margin-top: 1px; }
.page.compact3 .d-items { margin-top: 2px; }
.d-obj { display: grid; grid-template-columns: 1fr 1fr; column-gap: 20px; margin-top: 28px; text-align: center; align-items: start; }
.d-obj b { font-family: var(--display); font-size: 17px; }
.d-obj .v { font-size: 12px; }
.d-qty { display: flex; justify-content: center; gap: 30px; margin-top: 26px; }
.d-opts { margin-top: 12px; display: flex; flex-direction: column; align-items: center; }
.d-opt { display: flex; justify-content: center; gap: 10px; }
.d-opt .p { font-weight: 700; font-variant-numeric: tabular-nums; }
.d-menu { margin-top: 30px; display: flex; flex-direction: column; gap: 22px; }
.d-cake { margin-top: 26px; }
.d-cake .txt { line-height: 1.35; max-width: 430px; margin-top: 3px; }
/* Minimal tables: hairline rows, a dark rule under the header and above the total, no boxes */
.tbl { border-collapse: collapse; font-size: 11px; font-variant-numeric: lining-nums tabular-nums; }
.tbl td { border: 0; border-bottom: 0.5px solid #dcd8d3; padding: 3px 8px; }
.tbl td:first-child { padding-left: 0; }
.tbl td.c { text-align: center; }
.tbl td.r { text-align: right; }
.tbl td.b { font-weight: 700; white-space: nowrap; }
.tbl tr.h td { border-bottom: 1px solid #222; font-family: var(--ui); font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #6f6a65; padding-bottom: 3px; white-space: nowrap; }
.tbl tr.t td { border-top: 1px solid #222; border-bottom: 0; font-weight: 700; }
.tbl tr.p td { border-bottom: 0; font-weight: 700; padding-top: 2px; }
.tbl tr.lone td { border-bottom: 0.5px solid #dcd8d3; }
.d-wine { display: flex; flex-direction: column; align-items: center; text-align: center; margin-top: 18px; }
.d-wine .k { font-weight: 700; margin-bottom: 4px; }
.d-wine .tbl { margin-top: 4px; }
.d-wine .tbl, .d-cake .tbl { text-align: left; }
.d-wnote { font-family: var(--doc); font-size: 10px; font-weight: 400; font-style: italic; color: #555; margin-top: 2px; white-space: normal; }
.d-tot { margin: 22px auto 0; width: auto; }
.d-tot { border-top: 1px solid #222; }
.d-tot td { padding: 5px 0; font-size: 11px; }
.d-tot td.l { width: 290px; color: #333; }
.d-tot td.r { width: 110px; padding-right: 0; }
.d-tot tr.strong td { font-weight: 700; color: #111; }
.d-tot tr.acc td { border-bottom: 0; color: #d22b1f; font-weight: 700; }
.d-note { margin-top: 18px; text-align: center; font-size: 11px; }
/* Page 2: terms run the full width of the page, centred between equal margins */
.p2 { font-size: 11.5px; padding-left: 72px; padding-right: 72px; padding-bottom: 66px; }
.p2 h3 { font-family: var(--display); font-size: 19px; font-weight: 700; margin: 0 0 16px; text-align: center; letter-spacing: .02em; }
.p2 > .frame + h3 { margin-top: auto; } /* with the signature's auto margin this centres the text vertically */
.p2 .terms { width: 100%; display: flex; flex-direction: column; gap: 11px; text-align: justify; hyphens: auto; line-height: 1.5; }
.p2 .terms p { margin: 0; white-space: pre-line; }
.p2 .terms p.bold { font-weight: 700; }
.p2 .allergy { margin-top: 34px; }
.p2 .sign { margin-top: auto; padding-top: 40px; display: grid; grid-template-columns: 1fr 1fr; width: 80%; margin-left: auto; margin-right: auto; text-align: center; font-family: var(--display); font-size: 15px; font-weight: 700; }
.p2 .sign img { width: 200px; margin: 6px auto 0; display: block; }
.p2 .sign .c { text-align: center; }
/* Long menus: tighten page 1 step by step so it still fits one A4 */
.page.compact { font-size: 11px; line-height: 1.36; }
.page.compact .d-obj { margin-top: 22px; }
.page.compact .d-rule { margin: 14px auto 12px; }
.page.compact .d-qty, .page.compact .d-opts { margin-top: 14px; }
.page.compact .d-menu { margin-top: 20px; gap: 14px; }
.page.compact .d-cake, .page.compact .d-tot { margin-top: 16px; }
.page.compact .d-wine { margin-top: 12px; }
.page.compact .tbl td { padding-top: 2px; padding-bottom: 2px; }
.page.compact2 { font-size: 10.5px; line-height: 1.28; padding-top: 44px; }
.page.compact2 .d-obj { margin-top: 16px; }
.page.compact2 .d-rule { margin: 10px auto 8px; }
.page.compact2 .d-menu { gap: 9px; }
.page.compact2 .d-sec .k, .page.compact2 .d-wine .k { font-size: 14px; }
.page.compact3 { font-size: 10px; line-height: 1.2; padding-top: 38px; padding-bottom: 34px; }
.page.compact3 .d-obj { margin-top: 10px; }
.page.compact3 .d-qty { margin-top: 8px; } .page.compact3 .d-opts { margin-top: 4px; }
.page.compact3 .d-menu { margin-top: 10px; gap: 6px; }
.page.compact3 .d-sec .k, .page.compact3 .d-wine .k { font-size: 13px; }
.page.compact3 .d-cake, .page.compact3 .d-tot { margin-top: 8px; }
.page.compact3 .d-wine { margin-top: 6px; }
.page.compact3 .tbl td { padding-top: 1px; padding-bottom: 1px; }
.page.compact3 .d-tot td { padding: 2px 0; }
.offscreen { position: fixed; left: -12000px; top: 0; }
.offscreen .page { box-shadow: none; }

@media (prefers-reduced-motion: no-preference) { button { transition: background .12s, border-color .12s; } }
</style>

<div class="topbar">
  <div class="brand"><span class="dot" aria-hidden="true"></span><b>Preventivi Dvor</b><span id="curName">Osteria Dvor · Horeca FVG Srl</span></div>
  <div class="tb-group">
    <label class="f" style="flex-direction:row;align-items:center;gap:6px" for="preset"><span>Parti da</span>
      <select id="preset" style="width:auto">
        <option value="">un esempio…</option>
      </select>
    </label>
    <button id="btnNew" type="button">Nuovo</button>
    <button id="btnDup" type="button">Duplica</button>
    <button id="btnPdf" type="button" class="primary">Scarica PDF</button>
  </div>
  <div class="tb-group who" id="who" hidden></div>
</div>

<div class="layout">
  <div class="editor" id="editor"></div>
  <div class="preview">
    <div class="preview-bar">
      <div class="hint">Anteprima del preventivo · <b id="pvTotal"></b></div>
      <div class="warn" id="pvWarn" hidden>La pagina 1 è più lunga di un A4: nel PDF verrà ridotta per starci.</div>
    </div>
    <div class="pages-viewport" id="viewport"><div class="pages-scale" id="scaler"><div class="pages" id="pages"></div></div></div>
  </div>
</div>
<div id="toast" class="toast" hidden></div>

<script>
const LOGO = "data:image/png;base64,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";
const SIG = "data:image/png;base64,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";

/* ================= Catalog (from the Programma_preventivi sheets and past quotes) ================= */
const TAGLIERE = "Taglieri Salumi e Formaggi (Prosciutto San Daniele & Formaggi)";
const CATALOG = {
  aperitivo: {
    "Aperitivo": ["Pancetta cotta nel pane con kren","Frico croccante","Toc in braide","Toc in braide con funghi misti","Pollo in tempura","Polpettine","Frichetti","Frittatine","Lecca lecca di frico","Frico fritto",TAGLIERE,"Pulled Pork con insalatina sfiziosa di cavoli leggermente piccante","Cotto nel pane con senape e kren","Zucchine e melanzane fritte"]
  },
  antipasti: {
    "Menu 40": [TAGLIERE,"Polpettine","Frittatine","Lecca lecca di Frico","Carpaccio di Manzo"],
    "Menu 50": [TAGLIERE,"Frittatine","Frico fritto","Carpaccio di bresaola","Lonza Marinata con Salsa carpione","Lonza Marinata con Salsa carpione e kren","Tradizionali kipfel di patate serviti con maionese al tartufo","Tradizionali kipfel di patate","Flan di erbette di campo con fonduta di formaggi e guanciale croccante","Flan di erbette di campo con fonduta di formaggi (Opzione Vegetariana)","Flan di erbette di campo (Opzione Vegana)","Polpettine","Carpaccio di Manzo","Frichetti","Pancetta cotta nel pane con kren","Tortino di zucca con fonduta di formaggio di malga","Flan di Zucca con crema di formaggio di malga","Pulled Pork con insalatina sfiziosa di cavoli leggermente piccante","Pollo in tempura","Toc in braide con funghi misti"],
    "Menu 60-70": ["Taglieri Salumi e Formaggi (Salumi D'Osvaldo & Formaggi di Malga)","Tradizionali kipfel di patate serviti con maionese al tartufo","Pomodori Ripieni Gratinati","Lonza Marinata con Salsa carpione e kren","Salmone Marinato al pepe rosa","Flan di erbette di campo"],
    "Gaspare 50": ["Cotto nel pane con senape e kren","Frittata con erbe di campo formaggi misti del carso con le sue mostarde","Zucchine e melanzane fritte"],
    "Gaspare 65": ["Carpaccio di vitello cbt, con le pesche di fiumicello e la salsa carpione","Tortino di verdure con fonduta di formaggi di malga"],
    "Gaspare 75": ["Insalata di piovra con patate olive e pomodorini","Trittico di sardoni impanati, savor - marinati","Salmone marinato su letto di finocchio crudo condito con salsa alla birra e senape"]
  },
  primi: {
    "Menu 40": ["Pasticcio Tradizionale","Orzotto di verdure di stagione","Gnocchi sugo di arrosto"],
    "Menu 50": ["Fusi Dvor con Raguttino di Pollo, Zafferano, Riduzione di Basilico e Granelle di Pistacchio","Gnocchi con sugo di arrosto","Pasticcio tradizionale con fonduta di formaggi","Orzotto di frutti di mare","Crespelle con asparagi","Fuži Istriani con ragù di cortile","Fuži Istriani con ragù di San Daniele","Lasagna con ragù salsiccia","Orzotto con asparagi","Orzotto con sclopit","Orzotto con salsiccia e radicchio","Orzotto con funghi porcini","Toc in braide con funghi misti","Gnocchi con goulash","Pasta con il goulash cervo"],
    "Menu 60-70": ["Fusi Istriani profumati al tartufo nero e Gamberoni","Crespelle di verdure di stagione","Pasticcio Tradizionale con fonduta di formaggi","Orzotto di frutti di mare"],
    "Gaspare 50": ["Crespelle ai funghi","Fusi al refosco con prosciutto di San Daniele"],
    "Gaspare 65": ["Gnocchi alla griglia con i (pomodorini confit) verdure croccanti e pesto al basilico","Parmigiana di melanzane"],
    "Gaspare 75": ["Caponata con gamberi e zucchine al profumo di zafferano","Paccheri allo scoglio con il pescato del giorno"]
  },
  secondi: {
    "Menu 40": ["Ljubljanska alla griglia","Stinco di Maiale anteriore cucinato lentamente al forno con polenta"],
    "Menu 50": ["Goulash di manzo","Arrosto di vitello con funghi porcini","Arrosto di vitello","Guancette di manzo al refosco","Costicine di maiale sous vide","Scaloppa di sedano rapa in crosta di fiocchi di mais con vinaigrette di noci (Opzione Vegetariana)","Tartare di barbabietola rossa (Opzione Vegana)","Ljubljanska alla griglia","Stinco di Maiale anteriore cucinato lentamente al forno con polenta","Grigliata Mista (Čevapčiči, Spiedini, tagliata di manzo, pollo)","Filetto di maiale in crosta con prosciutto di S.Daniele","Lingua alla Wellington con spek e senape"],
    "Menu 60-70": ["Rollè di Tacchino","Guancette di manzo al refosco","Filetto di Maiale al pepe verde"],
    "Gaspare 50": ["Manzo all'Inglese con bufala campana e pomodorini","Arrosto di vitello con patate al forno"],
    "Gaspare 65": ["Filetto di maiale in crosta con prosciutto di San Daniele con salsa di senape e birra e patate al forno"],
    "Gaspare 75": ["Tortino di branzino con spinaci all'olio evo e scampo fatto in forno con la sua salsa"]
  },
  contorni: {
    "Menu 40 (2 contorni)": ["Patate al forno","Insalata mista"],
    "Menu 50 (3 contorni)": ["Contorni di stagione","Verdure grigliate","Patate al forno","Insalata mista","Verdure cotte","Spinaci al burro","Patate in tecia"],
    "Menu 60-70 (4 contorni)": ["Verdure cotte","Kipfel","Patate in tecia","Verdure grigliate","Patate al forno","Insalata mista","Spinaci al burro"]
  },
  sorbetto: { "Sorbetto": ["Sorbetto al limone e menta","Sorbetto al limone e fragola","Sorbetto alcolico"] },
  dessert: {
    "Menu 40": ["Tortino di Frutta di Stagione","Pannacotta"],
    "Menu 50": ["Tortino di Frutta di Stagione","Pannacotta","Palačinke con la Nutella o Marmellata","Tiramisù","Torta alle mele","Torta rustica alle ciliegie","Torta rustica alle pere e cioccolato","Sorbetto al limone e menta","Sorbetto alcolico","Carsolina"],
    "Menu 60-70": ["Tortino di Frutta di Stagione","Pannacotta","Tortino di cioccolato con il cuore morbido","Tiramisù"]
  },
  dolceBuffet: { "Brunch dolce": ["Brioches Assortite (Opzione vegana possibile)","Palancinke con la Nutella o Marmellata","Macedonia di Frutta Fresca","Strudel di mele","Parfait di yogurt greco, granola e coulis di frutta"] },
  salatoBuffet: { "Brunch salato": [TAGLIERE,"Frichetti (Convisi)","Frittatine","Couscous Verdure","Shakshuka con pane croccante"] }
};
// Added from the 2026 quotes (Carmine Bruno, Elisa Romano, Daniele, Ricky e Norma, Katja, Kogoj, Luca Marchioro, Niko Daneu, Helena Reimnitz)
const addTo = (sec, group, items) => { const g = CATALOG[sec][group] = CATALOG[sec][group] || []; items.forEach(i => { if (!g.includes(i)) g.push(i); }); };
addTo("antipasti", "Menu 40", ["Lonza Marinata con Salsa carpione"]);
addTo("primi", "Menu 40", ["Pasticcio di verdure","Pasta corta al ragù di cinghiale","Crespelle con asparagi","Parmigiana di melanzane","Orzotto con salsiccia e radicchio"]);
addTo("primi", "Menu 50", ["Gnocchi con sugo di goulash","Fusi con ragù di cortile","Crespelle di verdure di stagione"]);
addTo("primi", "Menu 60-70", ["Risotto con crema di zafferano rosso"]);
addTo("secondi", "Menu 40", ["Goulash di manzo","Grigliata Mista (Čevapčiči, Spiedini, tagliata di manzo, pollo)"]);
addTo("secondi", "Menu 50", ["Lonza di maiale alla birra"]);
addTo("secondi", "Menu 60-70", ["Costicine di agnello alla griglia"]);
addTo("contorni", "Menu 40 (2 contorni)", ["Verdure grigliate","Patate in tecia","Kipfel"]);
addTo("dessert", "Menu 40", ["Sorbetto analcolico"]);
addTo("dessert", "Menu 50", ["Sorbetto di limone e zenzero","Sorbetto al limone e fragola"]);
addTo("sorbetto", "Sorbetto", ["Sorbetto analcolico","Sorbetto di limone e zenzero"]);
addTo("antipasti", "Menu 40", ["Flan di verdure di stagione con fonduta di formaggi","Frichetti (Convisi)","Tradizionali kipfel di patate serviti con maionese al tartufo"]);
addTo("primi", "Menu 40", ["Orzotto con zafferano"]);
// handwritten extras in Programma_preventivi_Gaspare.xlsx
addTo("primi", "Gaspare 50", ["Pasticcio di carne"]);
addTo("secondi", "Gaspare 75", ["Turbante di branzino"]);
addTo("contorni", "Menu 50 (3 contorni)", ["Caponata"]);
addTo("salatoBuffet", "Brunch salato", ["Couscous alle verdure","Bresaola su letto di rucola"]);
// Menu pranzi e cene aziendali di Natale 2026 (un piatto a scelta per portata, base 45 €)
const NATALE = "Pranzi e cene aziendali di Natale 2026";
const NATALE_MENU = {
  antipasti: ["Toc' in braide","Flan di funghi con guanciale","Fricchetti friulani","Vitello tonnato","Carpaccio di manzo su rucola e riduzione di melograno","Rosa di prosciutto di Osvaldo con spuma di frico"],
  primi: ["Gnocchi al goulash","Gnocchi al radicchio rosso e formaggi","Risotto di zucca e amaretti","Risotto porro e salsiccia","Risotto radicchio e salsiccia","Lasagna classica"],
  dessert: ["Tiramisù"],
  secondi: ["Arrosto di vitello con patate al forno","Arrosto di maiale con patate in tecia","Guancette brasate al vino rosso con polentina morbida","Pancetta in crosta con finocchi","Filetto di maiale alla Wellington con purè","Scaloppa di sedano rapa con salsa olandese"]
};
Object.entries(NATALE_MENU).forEach(([sec, items]) => addTo(sec, NATALE, items));
// Piatti vegetariani: nel PDF hanno la fogliolina
const VEG = new Set(["Toc' in braide","Fricchetti friulani","Gnocchi al radicchio rosso e formaggi","Risotto di zucca e amaretti","Scaloppa di sedano rapa con salsa olandese"]);
// Supplemento a persona per alcuni piatti del menu di Natale (45 €)
const SUPPL = { "Rosa di prosciutto di Osvaldo con spuma di frico": 5, "Pancetta in crosta con finocchi": 5, "Filetto di maiale alla Wellington con purè": 5 };
const LEAF = `<svg class="leaf" viewBox="0 0 16 16" aria-label="vegetariano" role="img"><path d="M14 2C7 2 2.5 5.5 2.5 10.5c0 1.2.3 2.3.8 3.2C5 9.8 7.8 7.4 11 6c-2.8 1.8-5.2 4.6-6.6 8 1 .5 2 .7 3.1.7C12.6 14.7 14 9 14 2z" fill="#3f8f3a"/></svg>`;
// Preventivi di Natale: un rametto di agrifoglio al posto della linea rossa e gli auguri in fondo
const isNatale = q => /natal|christmas|xmas/i.test(q.oggetto || "");
// Pranzi e cene aziendali di Natale: si sceglie solo dal menu di Natale, che negli altri preventivi non compare
const isNataleAz = q => isNatale(q) && /aziendal|corporate|company/i.test(q.oggetto || "");
const HOLLY = `<svg class="holly" viewBox="0 0 64 22" aria-hidden="true"><path d="M31 12C26 4 17 3 9 7c3 1 4 3 3 5 3-1 5 0 6 2 2-2 5-2 7-1-1-3 1-4 6-1z" fill="#2f6b3a"/><path d="M33 12c5-8 14-9 22-5-3 1-4 3-3 5-3-1-5 0-6 2-2-2-5-2-7-1 1-3-1-4-6-1z" fill="#3f8f4a"/><circle cx="29.5" cy="14.5" r="3" fill="#c8432c"/><circle cx="34.5" cy="14.5" r="3" fill="#c8432c"/><circle cx="32" cy="10.2" r="3" fill="#b03424"/></svg>`;
const EN_APPETIZERS = ["Cold cuts and cheese boards (San Daniele prosciutto & cheeses)","Meatballs (small)","Frico lollipops","Arancini"];
addTo("antipasti", "English", EN_APPETIZERS);
addTo("aperitivo", "English", EN_APPETIZERS);
addTo("aperitivo", "Aperitivo", ["Arancini"]);
CATALOG.pizze = { "Pizze": ["Pizza Gourmet","Pizza Tradizionale","Pizza Vegetariana","Pizza Vegana"], "English": ["Pizza Gourmet","Pizza Traditional","Pizza Vegetarian","Pizza Vegan"] };
addTo("contorni", "English", ["Mix Grilled Veggies","Kipfel (traditional mashed-potato snacks) served with truffle mayo"]);

const SECTIONS = [
  ["aperitivo","Aperitivo:"],["antipasti","Antipasti:"],["primi","Primi:"],["secondi","Secondi:"],["pizze","Pizze:"],
  ["contorni","Contorni:"],["sorbetto","Sorbetto:"],["dessert","Dessert:"],
  ["dolceBuffet","Dolce:"],["salatoBuffet","Salato:"]
];
const SECTION_LABELS = {
  it: Object.fromEntries(SECTIONS),
  en: { aperitivo: "Aperitif:", antipasti: "Appetizers:", primi: "First Courses:", secondi: "Main Courses:", pizze: "Pizzas:", contorni: "Sides:", sorbetto: "Sorbet:", dessert: "Dessert:", dolceBuffet: "Sweet:", salatoBuffet: "Savory:" }
};
const WINES_CASA = ["Blazevi Goriška Brda Sivi Pinot","Blazevi Goriška Brda Merlot","Blazevi Goriška Brda Refošk","Rosso Sfuso","Bianco Sfuso","Bollicine Sfuso","Red house wine on tap","White house wine on tap"];
const BEERS = ["Birra alla spina Pilsner","Birra alla spina Lager","Birra alla spina IPA","Tap Beer Pilsner","Tap Beer Lager","Tap Beer IPA"];
const WINES_BOTT = ["Piedimont Ribolla Metodo Classico 12 mesi","Piedimont Cuvée Metodo Classico 36 mesi","Piedimont Cuvée Magnum","Pittaro Rosè Metodo Classico","Blazevi Goriška Brda Sivi Pinot","Blazevi Goriška Brda Merlot","Blazevi Goriška Brda Refošk","Blazevi Goriška Brda Rumena Rebula","Blazevi Goriška Brda Rumena Rebula Charmat","Friulano Collio DOC","Malvasia Collio DOC","Chardonnay Collio DOC","Sauvignon Collio DOC","Ribolla Collio DOC","Merlot Collio DOC","Refosco Collio DOC","Cabernet Collio DOC"];
const EVENT_TYPES = ["Battesimo","Comunione","Cresima","Matrimonio","Fidanzamento","Promessa di matrimonio","Anniversario","Anniversario di matrimonio","Compleanno","Diciottesimo","Laurea","Festa Privata","Festa","Festa Danzante","Festa Pensione","Pre Wedding Party","Festa di Natale","Pranzo aziendale di Natale","Cena aziendale di Natale","Festa di fine anno","Baby shower","Addio al celibato","Addio al nubilato","Rimpatriata","Cena di classe","Cena aziendale","Pranzo aziendale","Evento aziendale","Pranzo","Cena","Brunch","Aperitivo","Rinfresco","Degustazione","Pranzo commemorativo"];

const ALLERGIE = `Il nostro servizio di catering si impegna a garantire la sicurezza e la soddisfazione dei nostri clienti offrendo un'ampia selezione di piatti per soddisfare diverse esigenze alimentari. Tuttavia, vi preghiamo di notare quanto segue:

-Allergie Alimentari: È fondamentale informarci di eventuali allergie alimentari, intolleranze o sensibilità specifiche. Anche se adottiamo misure di sicurezza per evitare la contaminazione incrociata, non possiamo garantire l'assenza totale di allergeni nei nostri piatti. Pertanto, non possiamo assumerci responsabilità per eventuali reazioni allergiche che potrebbero verificarsi.

-Preferenze Alimentari: Forniamo opzioni vegetariane, vegane e senza glutine, ma vi invitiamo a comunicarci tempestivamente le vostre esigenze specifiche. Ciò ci consente di personalizzare il menu secondo le vostre preferenze.`;

const ALLERGIE_EN = `Our catering service is committed to ensuring the safety and satisfaction of our clients by offering a wide selection of dishes to meet various dietary needs. However, please note the following:

-Food Allergies: It is essential to inform us of any food allergies, intolerances or specific sensitivities. Although we take safety measures to avoid cross-contamination, we cannot guarantee the total absence of allergens in our dishes. Therefore, we cannot be held responsible for any allergic reactions that may occur.

-Dietary Preferences: We provide vegetarian, vegan, and gluten-free options, but we ask that you inform us promptly of your specific requirements. This allows us to customize the menu according to your preferences.

-Associated Risks: We advise each client to carefully assess their dietary needs and to consult a healthcare professional if in doubt about allergies or intolerances.`;

const TERMS = {
  eventi: {
    name: "Eventi", allergie: ALLERGIE,
    terms: `-Sarà richiesto un anticipo pari al 50% dell’importo totale al momento dell'accettazione del preventivo da versare su IBAN: IT21A0863112400000001044257 a nome di HORECA FVG SRL, il restante 50% potrà essere pagato il giorno dell'evento.

-Il pane è incluso nel prezzo mentre tutto il resto come superalcolici, cocktail, analcolici, e vini non specificati nel preventivo saranno conteggiati a parte.

-I caffè non sono inclusi nel preventivo e saranno conteggiati a parte.

-Il costo per i bambini fino a 10 anni è di 15 euro a testa, con un menù a parte che include pasta al pomodoro, pasta al ragù, patatine fritte e succhi di frutta in caraffa.

-Qualora la torta venga fornita dall'esterno, dovrà essere accompagnata da scontrino fiscale. Verrà applicata un'addizionale di €2,00 per ospite per il servizio di taglio, impiattamento e conservazione nella nostra cella frigorifera.

-È vietata qualsiasi diffusione di musica all'esterno del locale. La musica è consentita solo all'interno e esclusivamente durante eventi autorizzati. Il titolare può richiedere l'immediata cessazione e adottare provvedimenti in caso di violazione.

-Tutte le spese relative ai diritti d’autore e alla loro gestione connesse all’utilizzo di musica durante l’evento, incluse le tariffe SIAE, saranno interamente a carico del Cliente.`,
    after: ""
  },
  brunch: {
    name: "Brunch", allergie: ALLERGIE,
    terms: `-Sarà richiesto un anticipo pari al 50% dell’importo totale al momento dell'accettazione del preventivo da versare su IBAN: IT21A0863112400000001044257 a nome di HORECA FVG SRL, il restante 50% potrà essere pagato il giorno dell'evento.

-In caso di pioggia si potrà comunque usufruire del giardino con la possibilità di noleggio di gazebi per un prezzo unitario di 100 euro da aggiungere al preventivo finale.

-Il pane è incluso nel prezzo mentre tutto il resto come superalcolici, cocktail, analcolici, e vini non specificati nel preventivo saranno conteggiati a parte.

-I caffè non sono inclusi nel preventivo e saranno conteggiati a parte.

-Succhi di frutta in caraffa inclusi.`,
    after: `-Tutte le spese relative ai diritti d’autore e alla loro gestione connesse all’utilizzo di musica durante l’evento, incluse le tariffe SIAE, saranno interamente a carico del Cliente.

-In allegato trova anche la nostra lista dei vini, si applicherà 10% di sconto su Metodo Classico Piedimont Ribolla Gialla e Piedimont Cuvée in formato magnum.`
  },
  giardino: {
    name: "Eventi con giardino e gazebi", allergie: ALLERGIE,
    terms: `-Sarà richiesto un anticipo pari al 50% dell’importo totale al momento dell'accettazione del preventivo da versare su IBAN: IT21A0863112400000001044257 a nome di HORECA FVG SRL, il restante 50% potrà essere pagato il giorno dell'evento.

-In caso di pioggia si potrà comunque usufruire del giardino ma con il noleggio extra dei gazebi per un prezzo unitario di 100 euro da aggiungere al preventivo finale.

-Il pane è incluso nel prezzo mentre tutto il resto come superalcolici, cocktail, analcolici, e vini non specificati nel preventivo saranno conteggiati a parte.

-I caffè non sono inclusi nel preventivo e saranno conteggiati a parte.

-Il costo per i bambini fino a 10 anni è di 15 euro a testa, con un menù a parte che include pasta al pomodoro, pasta al ragù, patatine fritte e succhi di frutta in caraffa.`,
    after: `-Rischi Connessi: Consigliamo a ogni cliente di valutare attentamente le proprie esigenze alimentari e di consultare un professionista della salute in caso di dubbi riguardanti allergie o intolleranze alimentari.

-Tutte le spese relative ai diritti d’autore e alla loro gestione connesse all’utilizzo di musica durante l’evento, incluse le tariffe SIAE, saranno interamente a carico del Cliente.

-In allegato trova anche la nostra lista dei vini, si applicherà 10% di sconto su Metodo Classico Piedimont Ribolla Gialla e Piedimont Cuvée in formato magnum.`
  },
  en: {
    name: "English", allergie: ALLERGIE_EN,
    terms: `-A deposit equal to 50% of the total amount will be required upon acceptance of the quotation, to be paid to IBAN: IT21A0863112400000001044257 in the name of HORECA FVG SRL, and the remaining 50% may be paid on the day of the event.

-Please note: in case of bad weather our maximum indoor capacity is 90 people. However, gazebos can be rented so that all guests can be seated outdoors.

-Additional rental of gazebos at a unit price of €100 to be added to the final invoice.

-Bread is included in the price, while everything else such as spirits, cocktails, and wines not specified in the quotation will be charged separately.

-Coffee is not included in the quotation and will be charged separately.

-Soft drinks included in the price: cola, fanta, tonic water, iced tea, and assorted fruit juices (in carafes)

-The cost for children up to 10 years old is €15 per child, with a separate menu that includes tomato pasta, ragù pasta, French fries and fruit juices served in a pitcher.`,
    after: `-Attached you will also find our wine list; a 10% discount will apply to Metodo Classico Piedimont Ribolla Gialla and Piedimont Cuvée in magnum format.`
  }
};

/* ================= Model ================= */
const pad = n => String(n).padStart(2, "0");
const todayISO = () => { const d = new Date(); return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`; };
// Quote number: date of the quote (DDMMYYYY) + two letters + increasing counter, e.g. 22102026PV001
const START_TIMES = []; for (let h = 8; h < 24; h++) for (const m of ["00", "15", "30", "45"]) START_TIMES.push(pad(h) + ":" + m);
const NUM_RE = /^(\d{8})([A-Za-z]{2})(\d+)$/;
function numDate(iso){ const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso || ""); return m ? m[3] + m[2] + m[1] : numDate(todayISO()); }
function makeNumber(iso, letters, seq){ return numDate(iso) + letters + String(seq).padStart(3, "0"); }
function getLetters(){ return (window.DVOR_DATA && window.DVOR_DATA.letters) || (() => { try { return localStorage.getItem("dvor-preventivi-v1-letters"); } catch { return null; } })() || "PV"; }
const uid = () => Math.random().toString(36).slice(2, 10) + Date.now().toString(36);

function blankSections(){ return SECTIONS.map(([key,label]) => ({ key, label, sub: "", items: [] })); }
function blankQuote(){
  return {
    id: uid(), updated: Date.now(),
    cliente: "", email: "", telefono: "", numero: "", data: todayISO(),
    oggetto: "Battesimo", dataEvento: "", oraInizio: "", orario: "",
    adulti: 20, bambini: 0, prezzoBambino: 15,
    opzioni: [{ prezzo: 50, label: "" }],
    sezioni: blankSections(),
    torta: { on: false, grammi: 150, euroKg: 50 },
    vinoCasa: { on: true, prezzo: 15, incluso: false, righe: [{ nome: "Blazevi Goriška Brda Sivi Pinot", bott: 2, calici: "" }, { nome: "Blazevi Goriška Brda Merlot", bott: 2, calici: "" }] },
    vinoBott: { on: true, prezzo: 30, incluso: false, righe: [{ nome: "Piedimont Ribolla Metodo Classico 12 mesi", bott: 2, calici: "" }] },
    birra: { on: false, prezzo: 0, incluso: true, righe: [{ nome: "Birra alla spina Pilsner", bott: 1, calici: "" }] },
    supplScelti: [], lang: "it", acconto: 50, accontoNoTorta: false, accontoLabel: "", accontoFisso: "", nota: "",
    termsKey: "eventi", terms: TERMS.eventi.terms, termsAfter: TERMS.eventi.after, allergie: ALLERGIE
  };
}
const IT25 = "-Sarà richiesto un anticipo pari al 25% dell’importo totale al momento dell'accettazione del preventivo, 25% al massimo tre settimane prima dell’evento da versare su IBAN: IT21A0863112400000001044257 a nome di HORECA FVG SRL, il restante 50% potrà essere pagato il giorno dell'evento.", IT50 = "-Sarà richiesto un anticipo pari al 50% dell’importo totale al momento dell'accettazione del preventivo da versare su IBAN: IT21A0863112400000001044257 a nome di HORECA FVG SRL, il restante 50% potrà essere pagato il giorno dell'evento.";
const EN25 = '-A deposit equal to 25% of the total amount will be required upon acceptance of the quotation, 25% no later than three weeks before the event to be paid to IBAN: IT21A0863112400000001044257 in the name of HORECA FVG SRL, and the remaining 50% may be paid on the day of the event.', EN50 = '-A deposit equal to 50% of the total amount will be required upon acceptance of the quotation, to be paid to IBAN: IT21A0863112400000001044257 in the name of HORECA FVG SRL, and the remaining 50% may be paid on the day of the event.';
function migrate(q){
  const b = blankQuote();
  for (const k of Object.keys(b)) if (q[k] === undefined) q[k] = b[k];
  q.acconto = 50;
  if (typeof q.terms === "string") q.terms = q.terms.replace(IT25, IT50).replace(EN25, EN50).replace('-Acqua (frizzante-naturale) e pane sono inclusi nel prezzo mentre tutto il resto come superalcolici, cocktail, analcolici, e vini non specificati nel preventivo saranno conteggiati a parte.', '-Il pane è incluso nel prezzo mentre tutto il resto come superalcolici, cocktail, analcolici, e vini non specificati nel preventivo saranno conteggiati a parte.').replace('-Water (sparkling-still) and bread are included in the price, while everything else such as spirits, cocktails, and wines not specified in the quotation will be charged separately.', '-Bread is included in the price, while everything else such as spirits, cocktails, and wines not specified in the quotation will be charged separately.');
  if (typeof q.terms === "string" && !/caff[èe] non sono inclusi|Coffee is not included/i.test(q.terms)) {
    q.terms = q.terms.replace(/(-Il pane è incluso nel prezzo[^\n]*)/, "$1\n\n-I caffè non sono inclusi nel preventivo e saranno conteggiati a parte.").replace(/(-Bread is included in the price[^\n]*)/, "$1\n\n-Coffee is not included in the quotation and will be charged separately.");
    q.terms = q.terms.replace("-Caffetteria (Opzione latte soya) incluso e succhi di frutta in caraffa inclusi.", "-Succhi di frutta in caraffa inclusi.");
  }
  ["vinoCasa","vinoBott","birra"].forEach(k => { if (q[k].titolo === undefined) q[k].titolo = ""; });
  SECTIONS.forEach(([key, label]) => { if (!q.sezioni.some(s => s.key === key)) q.sezioni.push({ key, label: SECTION_LABELS[q.lang || "it"][key], sub: "", items: [] }); });
  const order = SECTIONS.map(s => s[0]);
  q.sezioni.sort((a, b) => order.indexOf(a.key) - order.indexOf(b.key));
  return q;
}
function setLang(q, lang){
  const from = q.lang || "it"; if (from === lang) return q;
  q.lang = lang;
  q.sezioni.forEach(s => { if (!s.label || s.label === SECTION_LABELS[from][s.key]) s.label = SECTION_LABELS[lang][s.key]; });
  const key = lang === "en" ? "en" : (q.termsKey === "en" ? "eventi" : q.termsKey);
  const T = TERMS[key]; q.termsKey = key; q.terms = T.terms; q.termsAfter = T.after; q.allergie = T.allergie;
  return q;
}
function useTerms(q, key){ const T = TERMS[key]; Object.assign(q, { termsKey: key, terms: T.terms, termsAfter: T.after, allergie: T.allergie }); return q; }
// m = { sectionKey: [items, red note, label] }
function menuScelte(q, m){
  q.sezioni.forEach(s => { const v = m[s.key]; if (v) { s.items = v[0].slice(); s.sub = v[1] || ""; if (v[2]) s.label = v[2]; } });
  return q;
}
const rows = (list) => list.map(([nome, bott, calici]) => ({ nome, bott, calici: calici ?? "" }));
function withSections(q, map){
  q.sezioni.forEach(s => { const v = map[s.key]; if (v) { s.items = v.items || v; if (v.sub) s.sub = v.sub; if (v.label) s.label = v.label; } });
  return q;
}
// Esempi di Natale: stesso menu, pranzo o cena
function nataleQuote(oggetto){ const q = blankQuote();
  Object.assign(q, { oggetto, adulti: 30, bambini: 0, opzioni: [{prezzo:45,label:"Antipasto, primo, secondo e dolce"}] });
  q.vinoCasa = { on: true, prezzo: 15, incluso: true, titolo: "", righe: rows([["Rosso Sfuso",3],["Bianco Sfuso",3]]) };
  q.vinoBott.on = false;
  return menuScelte(q, { antipasti: [NATALE_MENU.antipasti, "Scelta possibile 1 antipasto"],
    primi: [NATALE_MENU.primi, "Scelta possibile 1 primo"],
    secondi: [NATALE_MENU.secondi, "Scelta possibile 1 secondo"],
    dessert: [NATALE_MENU.dessert, ""] });
}
const PRESETS = [
  { group: NATALE, name: "Pranzo aziendale di Natale · 45 €", make(){ return nataleQuote("Pranzo aziendale di Natale"); } },
  { group: NATALE, name: "Cena aziendale di Natale · 45 €", make(){ return nataleQuote("Cena aziendale di Natale"); } },
  { name: "Battesimo 50 € con torta (come Sara Mesiano)", make(){ const q = blankQuote();
      Object.assign(q, { oggetto: "Battesimo", adulti: 20, bambini: 8, opzioni: [{prezzo:50,label:""}], torta: {on:true,grammi:150,euroKg:50} });
      q.vinoBott.righe = [{ nome: "Piedimont Ribolla Metodo Classico 12 mesi", bott: 4, calici: 40 }];
      return withSections(q, { antipasti: ["Tortino di zucca con fonduta di formaggio di malga"], primi: ["Orzotto con salsiccia e radicchio"], secondi: ["Arrosto di vitello con funghi porcini"], contorni: ["Contorni di stagione"], sorbetto: ["Sorbetto al limone e menta"], dessert: ["Tortino di Frutta di Stagione"] }); } },
  { name: "Battesimo 55 € con aperitivo", make(){ const q = blankQuote();
      Object.assign(q, { oggetto: "Battesimo", orario: "Ore 12:00", adulti: 38, bambini: 0, opzioni: [{prezzo:55,label:""}] });
      q.vinoCasa.righe = [{ nome: "Blazevi Goriška Brda Sivi Pinot", bott: 4, calici: "" }, { nome: "Blazevi Goriška Brda Merlot", bott: 4, calici: "" }];
      q.vinoBott.righe = [{ nome: "Piedimont Ribolla Metodo Classico 12 mesi", bott: 5, calici: 38 }];
      return withSections(q, { aperitivo: ["Pancetta cotta nel pane con kren","Frico croccante","Toc in braide","Pollo in tempura"], primi: ["Gnocchi con goulash"], secondi: ["Lingua alla Wellington con spek e senape"], contorni: ["Contorni di stagione"] }); } },
  { name: "Anniversario 50 € (torta 40 €/kg)", make(){ const q = blankQuote();
      Object.assign(q, { oggetto: "Anniversario", orario: "Pranzo", adulti: 26, bambini: 0, opzioni: [{prezzo:50,label:""}], torta: {on:true,grammi:150,euroKg:40} });
      q.vinoBott.righe = [{ nome: "Piedimont Ribolla Metodo Classico 12 mesi", bott: 3, calici: 30 }];
      return withSections(q, { aperitivo: ["Pancetta cotta nel pane con kren"], antipasti: ["Tortino di zucca con fonduta di formaggio di malga"], primi: ["Fuži Istriani con ragù di San Daniele"], secondi: ["Stinco di Maiale anteriore cucinato lentamente al forno con polenta"], contorni: ["Contorni di stagione"], dessert: ["Sorbetto al limone e menta"] }); } },
  { name: "Compleanno aperitivo 45 € / con primo 55 €", make(){ const q = blankQuote();
      Object.assign(q, { oggetto: "Compleanno", orario: "Orario: 19:00", adulti: 12, bambini: 0, opzioni: [{prezzo:45,label:"Solo aperitivo"},{prezzo:55,label:"Aperitivo più primo"}] });
      q.vinoCasa.righe = [{ nome: "Blazevi Goriška Brda Sivi Pinot", bott: 1, calici: "" }, { nome: "Blazevi Goriška Brda Merlot", bott: 1, calici: "" }];
      q.vinoBott.righe = [{ nome: "Piedimont Ribolla Metodo Classico 12 mesi", bott: 2, calici: 15 }];
      return withSections(q, { aperitivo: ["Polpettine","Frichetti","Pollo in tempura",TAGLIERE,"Toc in braide con funghi misti"], primi: ["Orzotto con funghi porcini"] }); } },
  { name: "Pranzo 45 € con torta", make(){ const q = blankQuote();
      Object.assign(q, { oggetto: "Pranzo", adulti: 30, bambini: 0, opzioni: [{prezzo:45,label:""}], torta: {on:true,grammi:150,euroKg:50} });
      q.vinoCasa.righe = [{ nome: "Rosso Sfuso", bott: 2, calici: "" }, { nome: "Bianco Sfuso", bott: 2, calici: "" }];
      q.vinoBott.on = false;
      return withSections(q, { antipasti: [TAGLIERE], primi: ["Orzotto con funghi porcini","Pasta con il goulash cervo"], dessert: ["Carsolina"] }); } },
  { name: "Brunch 25 € (buffet)", make(){ const q = blankQuote();
      useTerms(q, "brunch"); Object.assign(q, { oggetto: "Brunch", adulti: 25, bambini: 0, opzioni: [{prezzo:25,label:""}] });
      q.vinoCasa.on = false;
      q.vinoBott = { on: true, prezzo: 30, incluso: true, righe: [{ nome: "Piedimont Ribolla Metodo Classico 12 mesi", bott: 3, calici: 25 }] };
      return withSections(q, { dolceBuffet: { items: CATALOG.dolceBuffet["Brunch dolce"].slice(), sub: "Buffet\n3 scelte possibili" }, salatoBuffet: { items: CATALOG.salatoBuffet["Brunch salato"].slice(), sub: "Buffet:\n3 scelte possibili" } }); } },
  { name: "Battesimo 45 € a scelta (come Carmine Bruno)", make(){ const q = useTerms(blankQuote(), "giardino");
      Object.assign(q, { oggetto: "Battesimo", adulti: 20, opzioni: [{prezzo:45,label:""}], torta: {on:true,grammi:150,euroKg:50}, accontoNoTorta: true });
      q.vinoCasa = { on: true, prezzo: 15, incluso: true, titolo: "", righe: rows([["Rosso Sfuso",4],["Bianco Sfuso",4]]) };
      q.vinoBott = { on: true, prezzo: 30, incluso: true, titolo: "", righe: rows([["Piedimont Ribolla Metodo Classico 12 mesi",3]]) };
      return menuScelte(q, { antipasti: [[TAGLIERE,"Polpettine","Frittatine","Lecca lecca di Frico","Carpaccio di Manzo"], "Scelta possibile 3 antipasti", "Antipasti Buffet:"],
        primi: [["Pasticcio Tradizionale","Gnocchi sugo di arrosto","Pasticcio di verdure","Orzotto di verdure di stagione"], "Scelta possibile 2 primi (bis)"],
        secondi: [["Ljubljanska alla griglia","Stinco di Maiale anteriore cucinato lentamente al forno con polenta"], "Scelta possibile 1 secondo"],
        contorni: [["Verdure grigliate","Patate in tecia","Insalata mista","Kipfel"], "Scelta possibile 2 contorni"],
        dessert: [["Sorbetto analcolico","Pannacotta"], "Scelta possibile 1 dessert"] }); } },
  { name: "Battesimo 50 € a scelta (come Carmine Bruno)", make(){ const q = useTerms(blankQuote(), "giardino");
      Object.assign(q, { oggetto: "Battesimo", adulti: 20, opzioni: [{prezzo:50,label:""}], torta: {on:true,grammi:150,euroKg:50}, accontoNoTorta: true });
      q.vinoCasa = { on: true, prezzo: 15, incluso: true, titolo: "", righe: rows([["Rosso Sfuso",2],["Bianco Sfuso",2]]) };
      q.vinoBott = { on: true, prezzo: 30, incluso: true, titolo: "", righe: rows([["Merlot Collio DOC",1],["Cabernet Collio DOC",1],["Malvasia Collio DOC",1],["Ribolla Collio DOC",1],["Friulano Collio DOC",1],["Piedimont Ribolla Metodo Classico 12 mesi",3]]) };
      return menuScelte(q, { antipasti: [[TAGLIERE,"Tradizionali kipfel di patate serviti con maionese al tartufo","Lonza Marinata con Salsa carpione e kren","Frico fritto"], "Scelta possibile 3 antipasti", "Antipasti Buffet:"],
        primi: [["Fusi Dvor con Raguttino di Pollo, Zafferano, Riduzione di Basilico e Granelle di Pistacchio","Crespelle di verdure di stagione","Gnocchi con sugo di goulash","Orzotto di frutti di mare"], "Scelta possibile 2 primi (bis)"],
        secondi: [["Guancette di manzo al refosco","Arrosto di vitello","Costicine di maiale sous vide"], "Scelta possibile 1 secondo"],
        contorni: [["Verdure grigliate","Patate al forno","Insalata mista","Verdure cotte"], "Scelta possibile 3 contorni"],
        dessert: [["Tortino di Frutta di Stagione","Tiramisù","Pannacotta","Sorbetto di limone e zenzero"], "Scelta possibile 1 dolce"] }); } },
  { name: "Battesimo 70 € a scelta (come Carmine Bruno)", make(){ const q = useTerms(blankQuote(), "giardino");
      Object.assign(q, { oggetto: "Battesimo", adulti: 20, opzioni: [{prezzo:70,label:""}], torta: {on:true,grammi:150,euroKg:50}, accontoNoTorta: true });
      q.vinoCasa.on = false;
      q.vinoBott = { on: true, prezzo: 30, incluso: true, titolo: "", righe: rows([["Merlot Collio DOC",2],["Cabernet Collio DOC",1],["Refosco Collio DOC",1],["Friulano Collio DOC",2],["Chardonnay Collio DOC",1],["Sauvignon Collio DOC",1],["Malvasia Collio DOC",1],["Piedimont Ribolla Metodo Classico 12 mesi",3]]) };
      return menuScelte(q, { antipasti: [["Taglieri Salumi e Formaggi (Salumi D'Osvaldo & Formaggi di Malga)","Tradizionali kipfel di patate serviti con maionese al tartufo","Pomodori Ripieni Gratinati","Lonza Marinata con Salsa carpione e kren","Salmone Marinato al pepe rosa"], "Scelta possibile 3 antipasti", "Antipasti Buffet:"],
        primi: [["Fusi Istriani profumati al tartufo nero e Gamberoni","Crespelle di verdure di stagione","Orzotto di frutti di mare","Risotto con crema di zafferano rosso"], "Scelta possibile 2 primi (bis)"],
        secondi: [["Rollè di Tacchino","Guancette di manzo al refosco","Filetto di Maiale al pepe verde","Costicine di agnello alla griglia"], "Scelta possibile 1 secondo"],
        contorni: [["Verdure cotte","Kipfel","Patate in tecia","Verdure grigliate","Patate al forno","Insalata mista"], "Scelta possibile 3 contorni"],
        dessert: [["Tortino di Frutta di Stagione","Pannacotta","Tortino di cioccolato con il cuore morbido","Tiramisù"], "Scelta possibile 1 dolce"] }); } },
  { name: "Battesimo 45 € (come Ricky e Norma)", make(){ const q = useTerms(blankQuote(), "giardino");
      Object.assign(q, { oggetto: "Battesimo", adulti: 50, opzioni: [{prezzo:45,label:""}], torta: {on:true,grammi:150,euroKg:50}, accontoNoTorta: true });
      q.vinoCasa = { on: true, prezzo: 15, incluso: true, titolo: "", righe: rows([["Rosso Sfuso",5],["Bianco Sfuso",5]]) };
      q.vinoBott = { on: true, prezzo: 30, incluso: true, titolo: "", righe: rows([["Merlot Collio DOC",1],["Cabernet Collio DOC",1],["Friulano Collio DOC",1],["Malvasia Collio DOC",1],["Ribolla Collio DOC",1],["Piedimont Ribolla Metodo Classico 12 mesi",10],["Piedimont Cuvée Magnum",4]]) };
      return menuScelte(q, { antipasti: [[TAGLIERE,"Lecca lecca di Frico","Carpaccio di Manzo"], "Scelta possibile 3 antipasti", "Antipasti Buffet:"],
        primi: [["Pasta corta al ragù di cinghiale","Crespelle con asparagi"], "Scelta possibile 2 primi (bis)"],
        secondi: [["Ljubljanska alla griglia"], "Scelta possibile 1 secondo"],
        contorni: [["Patate al forno","Verdure grigliate"], "Scelta possibile 2 contorni"],
        dessert: [["Sorbetto analcolico"], "Scelta possibile 1 dessert"] }); } },
  { name: "Comunione 45 € con bambini (come Daniele)", make(){ const q = useTerms(blankQuote(), "giardino");
      Object.assign(q, { oggetto: "Comunione", orario: "11:00-20:00", adulti: 30, bambini: 10, opzioni: [{prezzo:45,label:""}], torta: {on:true,grammi:150,euroKg:50}, accontoNoTorta: true });
      q.vinoCasa = { on: true, prezzo: 15, incluso: true, titolo: "", righe: rows([["Rosso Sfuso",5],["Bianco Sfuso",5]]) };
      q.vinoBott = { on: true, prezzo: 30, incluso: true, titolo: "", righe: rows([["Cabernet Collio DOC",1],["Refosco Collio DOC",1],["Piedimont Cuvée Magnum",1]]) };
      return menuScelte(q, { antipasti: [[TAGLIERE,"Lonza Marinata con Salsa carpione"], "Scelta possibile 3 antipasti", "Antipasti Buffet:"],
        primi: [["Parmigiana di melanzane","Orzotto con salsiccia e radicchio"], "Scelta possibile 2 primi (bis)"],
        secondi: [["Goulash di manzo"], "Scelta possibile 1 secondo"],
        contorni: [["Patate al forno","Insalata mista"], "Scelta possibile 2 contorni"] }); } },
  { name: "Comunione 45 € (come Katja)", make(){ const q = blankQuote();
      Object.assign(q, { oggetto: "Comunione", adulti: 15, opzioni: [{prezzo:45,label:""}], torta: {on:true,grammi:150,euroKg:50}, accontoNoTorta: true });
      q.vinoCasa.on = false;
      q.vinoBott = { on: true, prezzo: 30, incluso: true, titolo: "", righe: rows([["Blazevi Goriška Brda Sivi Pinot",2],["Blazevi Goriška Brda Merlot",2],["Blazevi Goriška Brda Refošk",1],["Blazevi Goriška Brda Rumena Rebula Charmat",2]]) };
      return menuScelte(q, { antipasti: [[TAGLIERE,"Frittatine","Frico fritto","Polpettine","Tradizionali kipfel di patate"], "Scelta possibile 3 antipasti"],
        primi: [["Pasticcio tradizionale con fonduta di formaggi","Gnocchi con sugo di goulash","Crespelle di verdure di stagione","Orzotto con asparagi"], "Scelta possibile 2 primi (bis)"],
        secondi: [["Ljubljanska alla griglia","Grigliata Mista (Čevapčiči, Spiedini, tagliata di manzo, pollo)","Stinco di Maiale anteriore cucinato lentamente al forno con polenta"], "Scelta possibile 1 secondo"],
        contorni: [["Patate al forno","Spinaci al burro","Insalata mista","Verdure grigliate","Patate in tecia"], "Scelta possibile 3 contorni"] }); } },
  { name: "Battesimo 45 € con bambini (come Nicole Burello)", make(){ const q = useTerms(blankQuote(), "giardino");
      Object.assign(q, { oggetto: "Battesimo", orario: "11:00 - 18:00", adulti: 35, bambini: 10, opzioni: [{prezzo:45,label:""}], torta: {on:true,grammi:150,euroKg:50}, accontoNoTorta: true });
      q.terms += "\n\n-Offriremo a fine pasto due bottiglie di amaro alle erbe per tutti i partecipanti.";
      q.vinoCasa.on = false;
      q.vinoBott = { on: true, prezzo: 30, incluso: true, titolo: "", righe: rows([["Merlot Collio DOC",1],["Cabernet Collio DOC",2],["Friulano Collio DOC",1],["Malvasia Collio DOC",1],["Ribolla Collio DOC",1],["Piedimont Ribolla Metodo Classico 12 mesi",2]]) };
      return menuScelte(q, { antipasti: [[TAGLIERE,"Lonza Marinata con Salsa carpione","Flan di verdure di stagione con fonduta di formaggi","Frichetti (Convisi)"], "Scelta possibile 3 antipasti", "Antipasti Buffet:"],
        primi: [["Pasticcio Tradizionale","Orzotto con zafferano"], "Scelta possibile 2 primi (bis)"],
        secondi: [["Ljubljanska alla griglia"], "Scelta possibile 1 secondo"],
        contorni: [["Kipfel","Verdure grigliate","Patate in tecia"], "Scelta possibile 2 contorni"] }); } },
  { name: "Comunione 50 € con bambini (come Thomas Zotti)", make(){ const q = useTerms(blankQuote(), "giardino");
      Object.assign(q, { oggetto: "Comunione", adulti: 15, bambini: 5, opzioni: [{prezzo:50,label:""}], torta: {on:true,grammi:150,euroKg:50}, accontoNoTorta: true });
      q.vinoCasa.on = false;
      q.vinoBott = { on: true, prezzo: 30, incluso: true, titolo: "", righe: rows([["Cabernet Collio DOC",2],["Refosco Collio DOC",1],["Malvasia Collio DOC",1],["Ribolla Collio DOC",1],["Friulano Collio DOC",1],["Piedimont Ribolla Metodo Classico 12 mesi",3],["Piedimont Cuvée Magnum",2]]) };
      return menuScelte(q, { antipasti: [[TAGLIERE,"Frittatine","Carpaccio di bresaola"], "Scelta possibile 3 antipasti", "Antipasti Buffet:"],
        primi: [["Gnocchi con sugo di goulash","Pasticcio tradizionale con fonduta di formaggi"], "Scelta possibile 2 primi (bis)"],
        secondi: [["Arrosto di vitello"], "Scelta possibile 1 secondo"],
        contorni: [["Verdure grigliate","Patate al forno"], "Scelta possibile 2 contorni"],
        dessert: [["Sorbetto al limone e fragola"], "Scelta possibile 1 dolce"] }); } },
  { name: "Pranzo 45 € a scelta (come Sonia Borghese)", make(){ const q = blankQuote();
      Object.assign(q, { oggetto: "Pranzo", adulti: 20, opzioni: [{prezzo:45,label:""}], torta: {on:true,grammi:150,euroKg:50}, accontoNoTorta: true });
      q.terms = q.terms.split(/\n\s*\n/).filter(p => !/torta venga fornita/.test(p)).join("\n\n");
      q.vinoCasa = { on: true, prezzo: 15, incluso: true, titolo: "", righe: rows([["Rosso Sfuso",2],["Bianco Sfuso",2]]) };
      q.vinoBott = { on: true, prezzo: 30, incluso: true, titolo: "", righe: rows([["Blazevi Goriška Brda Sivi Pinot",1],["Blazevi Goriška Brda Merlot",1],["Blazevi Goriška Brda Refošk",1],["Blazevi Goriška Brda Rumena Rebula Charmat",3]]) };
      return menuScelte(q, { antipasti: [[TAGLIERE,"Frittatine","Polpettine","Lecca lecca di Frico","Carpaccio di bresaola"], "Scelta possibile 3 antipasti"],
        primi: [["Fusi con ragù di cortile","Crespelle di verdure di stagione","Pasticcio tradizionale con fonduta di formaggi","Lasagna con ragù salsiccia","Orzotto con asparagi"], "Scelta possibile 2 primi (bis)"],
        secondi: [["Costicine di maiale sous vide","Goulash di manzo","Ljubljanska alla griglia","Arrosto di vitello"], "Scelta possibile 1 secondo"],
        contorni: [["Spinaci al burro","Patate al forno","Insalata mista","Verdure grigliate","Verdure cotte"], "Scelta possibile 3 contorni"],
        dessert: [["Sorbetto al limone e fragola","Palačinke con la Nutella o Marmellata"], "Scelta possibile 1 dessert"] }); } },
  { name: "Compleanno 45 € aperitivo a buffet (come Mario Peresson)", make(){ const q = useTerms(blankQuote(), "giardino");
      Object.assign(q, { oggetto: "Compleanno", orario: "19:00", adulti: 50, opzioni: [{prezzo:45,label:""}], torta: {on:true,grammi:150,euroKg:50}, accontoNoTorta: true, nota: "1 celiaca e vegetariani" });
      q.vinoCasa.on = false;
      q.vinoBott = { on: true, prezzo: 30, incluso: true, titolo: "", righe: rows([["Piedimont Ribolla Metodo Classico 12 mesi",7]]) };
      return menuScelte(q, { antipasti: [[TAGLIERE,"Polpettine","Frittatine","Frichetti (Convisi)","Tradizionali kipfel di patate serviti con maionese al tartufo"], "", "Antipasti Buffet:"],
        primi: [["Orzotto di verdure di stagione"], ""] }); } },
  { name: "Pranzo aziendale 45 € (come Luca Marchioro)", make(){ const q = blankQuote();
      Object.assign(q, { oggetto: "Pranzo aziendale", adulti: 40, opzioni: [{prezzo:45,label:""}] });
      q.vinoCasa = { on: true, prezzo: 15, incluso: true, titolo: "", righe: rows([["Rosso Sfuso",3],["Bianco Sfuso",3]]) };
      q.vinoBott = { on: true, prezzo: 30, incluso: true, titolo: "", righe: rows([["Blazevi Goriška Brda Sivi Pinot",2],["Blazevi Goriška Brda Refošk",2],["Blazevi Goriška Brda Merlot",2],["Blazevi Goriška Brda Rumena Rebula",5]]) };
      return menuScelte(q, { antipasti: [[TAGLIERE,"Frichetti","Carpaccio di bresaola","Lonza Marinata con Salsa carpione"], "Scelta possibile 2 antipasti"],
        primi: [["Fuži Istriani con ragù di cortile","Crespelle con asparagi","Gnocchi con sugo di arrosto","Orzotto con sclopit","Pasticcio tradizionale con fonduta di formaggi","Lasagna con ragù salsiccia"], "Scelta possibile 2 primi (bis)"],
        secondi: [["Ljubljanska alla griglia","Costicine di maiale sous vide","Arrosto di vitello","Lonza di maiale alla birra"], "Scelta possibile 1 secondo"],
        contorni: [["Patate al forno","Insalata mista","Verdure grigliate","Spinaci al burro"], "Scelta possibile 2 contorni"],
        dessert: [["Sorbetto al limone e fragola"], ""] }); } },
  { name: "Brunch 30 € (come Niko Daneu)", make(){ const q = useTerms(blankQuote(), "brunch");
      Object.assign(q, { oggetto: "Brunch", adulti: 15, opzioni: [{prezzo:30,label:""}] });
      q.vinoCasa.on = false;
      q.vinoBott = { on: true, prezzo: 30, incluso: true, titolo: "", righe: rows([["Piedimont Ribolla Metodo Classico 12 mesi",2,20]]) };
      return menuScelte(q, { dolceBuffet: [CATALOG.dolceBuffet["Brunch dolce"], "Buffet\n3 scelte possibili"],
        salatoBuffet: [[TAGLIERE,"Frichetti (Convisi)","Frittatine","Couscous alle verdure","Bresaola su letto di rucola"], "Buffet:\n2 scelte possibili"] }); } },
  { name: "Brunch alla carta Dvor (prezzi del menu brunch)", make(){ const q = useTerms(blankQuote(), "brunch");
      Object.assign(q, { oggetto: "Brunch", adulti: 20 });
      q.vinoCasa.on = false; q.vinoBott.on = false;
      const pick = n => BRUNCH_CARTA.find(r => r[1].startsWith(n))[1];
      withSections(q, { antipasti: { items: [pick("Frico croccante"), pick("Polenta e pitina")], label: "Per cominciare:" },
        primi: { items: [pick("Uova Benedict")], label: "Uova:" },
        dessert: { items: [pick("French toast di gubana"), pick("Cheesecake")], label: "Dolci:" },
        aperitivo: { items: [pick("Cappuccino"), pick("Spremuta d'arancia"), pick("Mimosa del Collio")], label: "Caffetteria e drink:" } });
      q.opzioni = [{ prezzo: brunchSum(q), label: "" }];
      return q; } },
  { name: "Pre Wedding Party in inglese (come Helena Reimnitz)", make(){ const q = setLang(blankQuote(), "en");
      Object.assign(q, { oggetto: "Pre Wedding Party", orario: "5:30 - 12pm", adulti: 140, opzioni: [{prezzo:45,label:""}] });
      q.vinoCasa = { on: true, prezzo: 15, incluso: true, titolo: "Tap House Wines:", righe: rows([["Red house wine on tap",20],["White house wine on tap",20]]) };
      q.vinoBott.on = false;
      q.birra = { on: true, prezzo: 0, incluso: true, titolo: "Tap Beers:", righe: rows([["Tap Beer Pilsner",3],["Tap Beer Lager",3],["Tap Beer IPA",3]]) };
      return menuScelte(q, { antipasti: [EN_APPETIZERS, "", "Appetizers Buffet:"],
        pizze: [CATALOG.pizze.English, ""],
        contorni: [["Mix Grilled Veggies","Kipfel (traditional mashed-potato snacks) served with truffle mayo"], ""] }); } },
  { name: "Menu 50 € (cotto nel pane, manzo all'inglese)", make(){ const q = blankQuote();
      Object.assign(q, { adulti: 50, opzioni: [{prezzo:50,label:""}], torta: {on:true,grammi:150,euroKg:50} });
      return withSections(q, { antipasti: CATALOG.antipasti["Gaspare 50"].slice(), primi: ["Crespelle ai funghi","Parmigiana di melanzane"], secondi: { items: CATALOG.secondi["Gaspare 50"].slice(), sub: "Scelta possibile 1 secondo" }, sorbetto: ["Sorbetto al limone e menta"] }); } },
  { name: "Menu 65 € (carpaccio di vitello, filetto in crosta)", make(){ const q = blankQuote();
      Object.assign(q, { adulti: 50, opzioni: [{prezzo:65,label:""}], torta: {on:true,grammi:150,euroKg:50} });
      return withSections(q, { antipasti: CATALOG.antipasti["Gaspare 65"].slice(), primi: CATALOG.primi["Gaspare 65"].slice(), secondi: CATALOG.secondi["Gaspare 65"].slice(), sorbetto: ["Sorbetto al limone e menta"] }); } },
  { name: "Menu 75 € di pesce", make(){ const q = blankQuote();
      Object.assign(q, { adulti: 50, opzioni: [{prezzo:75,label:""}], torta: {on:true,grammi:150,euroKg:50} });
      return withSections(q, { antipasti: CATALOG.antipasti["Gaspare 75"].slice(), primi: CATALOG.primi["Gaspare 75"].slice(), secondi: CATALOG.secondi["Gaspare 75"].slice(), sorbetto: ["Sorbetto al limone e menta"] }); } }
];

/* ================= Calculations (same formulas as the Excel sheet) ================= */
const num = v => { const n = parseFloat(String(v).replace(",", ".")); return isFinite(n) ? n : 0; };
const r2 = n => Math.round(n * 100) / 100;
// Glasses per unit, as in the sheets: 1 L of house wine = 10, 0.75 L bottle = 7.5 (1 L Blazevi = 10), 30 L keg = 60 pints
function perBottle(kind, nome){
  if (kind === "birra") return 60;
  if (kind === "vinoCasa") return 10;
  return /blazevi/i.test(nome || "") && !/rebula/i.test(nome || "") ? 10 : 7.5;
}
function calici(row, kind){ return row.calici !== "" && row.calici != null ? num(row.calici) : Math.round(num(row.bott) * perBottle(kind, row.nome)); }
const DRINKS = ["vinoCasa", "vinoBott", "birra"];
function compute(q){
  const ad = num(q.adulti), bb = num(q.bambini);
  const kids = bb * num(q.prezzoBambino);
  const food = q.opzioni.map(o => r2(num(o.prezzo) * ad + kids));
  const kg = Math.round(num(q.torta.grammi) / 1000 * (ad + bb) * 100) / 100;
  const torta = q.torta.on ? r2(kg * num(q.torta.euroKg)) : 0;
  const d = {};
  DRINKS.forEach(k => {
    const w = q[k];
    const bott = w.on ? w.righe.reduce((s, r) => s + num(r.bott), 0) : 0;
    // total glasses as in the sheet: overrides as typed, the rest summed before rounding
    const cal = w.on ? w.righe.reduce((s, r) => s + (r.calici !== "" && r.calici != null ? num(r.calici) : 0), 0) + Math.round(w.righe.reduce((s, r) => s + (r.calici !== "" && r.calici != null ? 0 : num(r.bott) * perBottle(k, r.nome)), 0)) : 0;
    d[k] = { bott, cal, prezzo: w.on && !w.incluso ? r2(bott * num(w.prezzo)) : 0 };
  });
  const vini = r2(d.vinoCasa.prezzo + d.vinoBott.prezzo + d.birra.prezzo);
  // supplemento a persona (adulti) per i piatti con maggiorazione segnati come scelti
  const inMenu = new Set(q.sezioni.flatMap(s => s.items.map(i => i.trim())));
  const supp = (q.supplScelti || []).filter(n => inMenu.has(n)).reduce((a, n) => a + (SUPPL[n] || 0), 0);
  const sup = r2(supp * ad);
  const finale = food.map(f => r2(f + sup + vini + torta));
  const base = q.accontoNoTorta ? finale[0] - torta : finale[0];
  const acconto = r2(base * 0.5); // the deposit is always 50%
  return { food, kg, torta, d, vini, supp, sup, finale, acconto };
}

/* ================= Formatting ================= */
const MESI = ["gennaio","febbraio","marzo","aprile","maggio","giugno","luglio","agosto","settembre","ottobre","novembre","dicembre"];
function dateIT(iso){ if (!iso) return ""; const [y,m,d] = iso.split("-").map(Number); return `${d} ${MESI[m-1]} ${y}`; }
function dateEN(iso){ if (!iso) return ""; const [y,m,d] = iso.split("-").map(Number); return new Date(y, m-1, d).toLocaleDateString("en-US", { weekday: "long", year: "numeric", month: "long", day: "numeric" }); }
const eur = n => n.toLocaleString("it-IT", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " €";
const eurPlain = n => n.toLocaleString("it-IT", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const kgFmt = n => n.toLocaleString("it-IT", { maximumFractionDigits: 2 });
const esc = s => String(s ?? "").replace(/[&<>"]/g, c => ({ "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;" }[c]));
const lc = s => s ? s.charAt(0).toLowerCase() + s.slice(1) : "";

/* Quote wording in each language */
const L = {
  it: { tel: "Tel.", cliente: "Cliente:", numero: "Numero preventivo", data: "Data", oggetto: "Oggetto Preventivo", dataEvento: "Data Evento", inizio: "Inizio evento: ore", adulti: "Quantità adulti", bambini: "Quantità bambini", opzione: "Opzione:",
    torte: "Torte:", torteTxt: e => `Ci riforniamo da un partner di pasticceria che offre qualsiasi tipo di torta<br>con disegni o stampe a un prezzo vantaggioso di ${e} euro al kg.`, grammi: g => `${g} grammi circa a persona`, prezzo: "Prezzo",
    bottiglie: "Bottiglie", calici: "Calici", litri: "Litri", fusti: "Fusti (30 litri)", pinte: "Pinte", vinoCasa: "Vino della casa:", litro1: "Bottiglia da 1 litro", vinoBott: "Vino Bottiglia:", birra: "Birra alla spina:",
    food: "Prezzo food", vini: "Prezzo vini", torta: "Torta", finale: "Prezzo finale preventivo", conTorta: "Prezzo finale preventivo con torta", finaleTorta: "Prezzo finale con torta", totale: "Prezzo Totale",
    foodOpt: l => `Prezzo food ${l}`, suppl: e => `Supplemento piatti scelti (+${eurPlain(e).replace(",00", "")} € a persona)`, supplNota: "maggiorazione di 5 € a persona", finaleOpt: l => `Prezzo finale ${l}`, opz: i => `opzione ${i}`, acconto: "Acconto",
    termini: "Termini & Condizioni", allergie: "Allergie e Preferenze Alimentari", firmaCliente: "Cliente", date: dateIT },
  en: { tel: "Phone:", cliente: "Customer:", numero: "Quotation Number", data: "Date:", oggetto: "Subject of Quotation", dataEvento: "Date Event:", inizio: "Start time:", adulti: "Adults", bambini: "Kids", opzione: "Price Option (pp):",
    torte: "Cakes:", torteTxt: e => `We work with a pastry partner who can make any kind of cake<br>with designs or prints at a convenient price of ${e} euros per kg.`, grammi: g => `About ${g} grams per person`, prezzo: "Price",
    bottiglie: "Bottles", calici: "Glasses", litri: "Liters", fusti: "Kegs (30 liters)", pinte: "Pints", vinoCasa: "House Wines:", litro1: "1-liter bottle", vinoBott: "Bottled Wines:", birra: "Tap Beers:",
    food: "Food price", vini: "Drinks price", torta: "Cake", finale: "Final Price Quote", conTorta: "Final Price Quote with cake", finaleTorta: "Final price with cake", totale: "Total Price",
    foodOpt: l => `Food price ${l}`, suppl: e => `Supplement for chosen dishes (+${eurPlain(e).replace(",00", "")} € per person)`, supplNota: "supplement of 5 € per person", finaleOpt: l => `Final price ${l}`, opz: i => `option ${i}`, acconto: "Deposit",
    termini: "Terms & Conditions", allergie: "Allergies and Dietary Preferences", firmaCliente: "Client", date: dateEN }
};

/* ================= Storage (this browser only) ================= */
const LS_KEY = "dvor-preventivi-v1";
// On the staff server (PHP) quotes live in the shared database; as a standalone page they stay in this browser.
const SERVER = !!window.DVOR_SERVER;
async function api(r, opts = {}){
  const res = await fetch("api.php?r=" + r, { method: opts.method || "GET", credentials: "same-origin",
    headers: { "Content-Type": "application/json", "X-Dvor": "1" }, body: opts.body ? JSON.stringify(opts.body) : undefined });
  const d = await res.json().catch(() => ({}));
  if (res.status === 401) { location.href = "login.php"; throw new Error(d.error || "Sessione scaduta"); }
  if (!res.ok) throw new Error(d.error || "Errore del server");
  return d;
}
function setSync(msg, err){ const el = document.getElementById("sync"); if (el) { el.textContent = msg; el.classList.toggle("err", !!err); } }
const toPush = new Map(); let pushT;
function pushQuote(q){ if (!q) return; toPush.set(q.id, q); setSync("Salvataggio…"); clearTimeout(pushT); pushT = setTimeout(flushPush, 700); }
async function flushPush(){
  const list = [...toPush.values()]; toPush.clear();
  for (const q of list) {
    try { await api("quote&id=" + encodeURIComponent(q.id), { method: "POST", body: q }); setSync("Salvato sul server"); }
    catch (err) { toPush.set(q.id, q); setSync("Non salvato: " + err.message, true); clearTimeout(pushT); pushT = setTimeout(flushPush, 5000); }
  }
}
window.addEventListener("beforeunload", e => { if (SERVER && toPush.size) { e.preventDefault(); e.returnValue = ""; } });
function loadAll(){
  if (SERVER) { let cur = null; try { cur = localStorage.getItem(LS_KEY + "-cur"); } catch {} return { quotes: (window.DVOR_DATA && window.DVOR_DATA.quotes) || [], current: cur }; }
  try { const v = JSON.parse(localStorage.getItem(LS_KEY) || "null"); return v && Array.isArray(v.quotes) ? v : null; } catch { return null; }
}
function saveAll(){
  if (SERVER) { try { localStorage.setItem(LS_KEY + "-cur", store.current); } catch {} pushQuote(Q); return; }
  try { localStorage.setItem(LS_KEY, JSON.stringify(store)); } catch {}
}
async function setLetters(v){
  const L = String(v || "").toUpperCase().replace(/[^A-Z]/g, "");
  const f = $("#f_letters");
  if (L.length !== 2) { toast("Servono esattamente due lettere, es. PV"); if (f) f.value = getLetters(); return; }
  if (SERVER) { try { await api("letters", { method: "POST", body: { letters: L } }); } catch (err) { toast(err.message); if (f) f.value = getLetters(); return; } window.DVOR_DATA.letters = L; }
  else { try { localStorage.setItem(LS_KEY + "-letters", L); } catch {} }
  if (f) f.value = L;
  const m = NUM_RE.exec(Q.numero || ""); if (m && m[2] !== L) { Q.numero = m[1] + L + m[3]; refreshAll(); }
  toast("Lettere del numero: " + L);
}
// Gives q a new number. As a standalone page the counter is kept in this browser; on the server it is shared by all staff.
function localSeq(){
  let n = 0; try { n = +localStorage.getItem(LS_KEY + "-seq") || 0; } catch {}
  (store ? store.quotes : []).forEach(q => { const m = NUM_RE.exec(q.numero || ""); if (m) n = Math.max(n, +m[3]); });
  n++; try { localStorage.setItem(LS_KEY + "-seq", String(n)); } catch {}
  return n;
}
async function assignNumber(q){
  const letters = getLetters();
  if (!SERVER) { q.numero = makeNumber(q.data, letters, localSeq()); return; }
  q.numero = "";
  try {
    const d = await api("nextnumber", { method: "POST" });
    q.numero = makeNumber(q.data, d.letters || letters, d.seq);
  } catch (err) { toast("Numero non assegnato: " + err.message); return; }
  if (q === Q) { const f = document.getElementById("f_numero"); if (f) f.value = q.numero; const c = document.getElementById("curName"); if (c) c.textContent = (Q.cliente ? Q.cliente + " · " : "") + Q.numero; renderPreview(); }
  pushQuote(q);
}
let store = loadAll();
if (SERVER && !store.quotes.length) { const first = blankQuote(); store = { quotes: [first], current: first.id }; setTimeout(() => assignNumber(first)); }
if (!store || !store.quotes.length) { const first = PRESETS.find(p => !p.group).make(); first.cliente = "Esempio – Famiglia Rossi"; first.email = "rossi@example.com"; first.dataEvento = "2026-11-15"; store = { quotes: [first], current: first.id }; first.numero = makeNumber(first.data, getLetters(), localSeq()); }
store.quotes.forEach(migrate);
let Q = store.quotes.find(q => q.id === store.current) || store.quotes[0];

/* ================= Path helpers ================= */
function getPath(o, p){ return p.split(".").reduce((a, k) => a == null ? a : a[k], o); }
function setPath(o, p, v){ const ks = p.split("."); const last = ks.pop(); const t = ks.reduce((a, k) => a[k], o); t[last] = v; }

/* ================= Editor ================= */
const $ = s => document.querySelector(s);
const editor = $("#editor");
function field(label, path, opts = {}){
  const v = getPath(Q, path) ?? "";
  const id = "f_" + path.replace(/\./g, "_");
  const type = opts.type || "text";
  const cls = opts.full ? "f full" : "f";
  if (opts.textarea) return `<label class="${cls}" for="${id}">${label}<textarea id="${id}" data-p="${path}" rows="${opts.rows||4}">${esc(v)}</textarea></label>`;
  const list = opts.list ? ` list="${opts.list}"` : "";
  const extra = opts.step ? ` step="${opts.step}"` : "";
  const ph = opts.ph ? ` placeholder="${esc(opts.ph)}"` : "";
  const t = type === "number" ? "text\" inputmode=\"decimal" : type;
  return `<label class="${cls}" for="${id}">${label}<input id="${id}" type="${t}" data-p="${path}" value="${esc(v)}"${list}${extra}${ph}></label>`;
}
function customEvent(){ return !!Q.oggettoLibero || !EVENT_TYPES.includes(Q.oggetto); }
// The dropdown groups dishes only by price band (40 / 50 / 60-75 €); groups without a price keep their name
function bandOf(g){
  if (g === NATALE) return g;
  const m = g.match(/\d+/); if (!m) return g;
  const n = +m[0], note = (g.match(/\(.*\)/) || [""])[0];
  return (n <= 55 ? "Menu 45-55 €" : "Menu 60-75 €") + (note ? " " + note : "");
}
// Brunch alla carta (menu "Brunch Osteria Dvor"): [category, item, price €]
const BRUNCH_CARTA = [
  ["Per cominciare", "Patate dolci fritte con aioli all'aglio di Resia", 6.5],
  ["Per cominciare", "Frico croccante – cialda croccante di Montasio con salsa al cren", 7],
  ["Per cominciare", "Polenta e pitina – crostini di polenta abbrustolita, pitina della Val Tramontina e ricotta affumicata", 8],
  ["French toast & pancakes", "French toast di gubana (2 pz) – gubana dorata nell'uovo, con un goccio di grappa", 7],
  ["French toast & pancakes", "Pancakes (3 pz)", 6],
  ["French toast & pancakes", "Pancakes con cioccolato", 9],
  ["French toast & pancakes", "Pancakes con salsa ai lamponi e panna", 9],
  ["French toast & pancakes", "Pancakes con miele di acacia del Friuli", 8],
  ["French toast & pancakes", "Pancakes con fragole", 9],
  ["Bagel", "Bagel Tasty – trota affumicata di San Daniele, avocado, crème fraîche, uovo all'occhio di bue", 11],
  ["Bagel", "Bagel San Daniele – prosciutto crudo di San Daniele DOP, Montasio fresco, rucola e miele di castagno", 11],
  ["Dall'orto", "Caesar salad – misticanza, petto di pollo alla brace, salsa Caesar, crostini e scaglie di Montasio stravecchio", 13],
  ["Dall'orto", "Insalata di trota – misticanza, trota affumicata, avocado, mela friulana, sour cream e semi tostati", 13],
  ["Uova", "Uova Traditional – due uova a piacere, pancetta croccante, luganega friulana, fagioli e funghi", 11.5],
  ["Uova", "Uova Benedict – muffin salato, uovo in camicia, prosciutto di San Daniele, spinaci e crema d'uovo", 12],
  ["Uova", "Uova e frico – frico morbido di patate e Montasio, uovo all'occhio di bue e polenta", 12],
  ["Sapori di confine", "Cjarsons – ravioli carnici dolci e salati, burro fuso, ricotta affumicata e cannella", 12],
  ["Sapori di confine", "Muset e brovada – musetto caldo, brovada DOP, uovo in camicia e polenta abbrustolita", 13],
  ["Sapori di confine", "Tagliere del Collio – San Daniele DOP, pitina, Montasio fresco e stagionato, frico, polenta e mostarda", 15],
  ["Panini & burger", "Club sandwich – prosciutto cotto, Montasio, uovo alla piastra, pancetta croccante, insalata e pomodoro, con patate fritte", 12],
  ["Panini & burger", "Dvor burger – Angus 180 g alla brace, Montasio fuso, speck, pomodoro, lattuga e maionese al cren, con patate fritte", 16],
  ["Panini & burger", "Frico burger – Angus 180 g nel frico croccante, uovo all'occhio di bue, pancetta e cipolla alla birra, con patate fritte", 19],
  ["Dolci", "Yogurt bianco con miele di castagno e noci", 5],
  ["Dolci", "Gubana", 5],
  ["Dolci", "Gubana con un bicchierino di grappa o Ramandolo", 8],
  ["Dolci", "Cheesecake con salsa ai lamponi e lamponi freschi", 5],
  ["Caffetteria", "Espresso", 1.5],
  ["Caffetteria", "Doppio espresso", 3],
  ["Caffetteria", "Cappuccino", 2],
  ["Caffetteria", "Latte macchiato", 3],
  ["Caffetteria", "Caffè filtro (200 ml)", 3],
  ["Caffetteria", "Caffè corretto grappa", 3],
  ["Caffetteria", "Tè caldo o infuso", 3],
  ["Centrifugati", "Centrifugato Detox – mela verde friulana, zenzero, sedano", 6],
  ["Centrifugati", "Centrifugato ACE – carota, arancia, limone", 6],
  ["Centrifugati", "Spremuta d'arancia", 4],
  ["Breakfast cocktails", "Bacon old fashioned – bourbon al bacon, sciroppo di zucchero, angostura, scorza d'arancia", 9],
  ["Breakfast cocktails", "Bloody Dvor – grappa bianca, succo di pomodoro, cren, tabasco, worcester, limone", 9],
  ["Breakfast cocktails", "Sbagliato di Ribolla – bitter, vermouth rosso, Ribolla Gialla spumante del Collio", 9],
  ["Breakfast cocktails", "Mimosa del Collio – Ribolla Gialla spumante e spremuta d'arancia", 8],
  ["Breakfast cocktails", "Spritz Furlan – Friulano del Collio, bitter, soda, scorza d'arancia", 8]
];
const BRUNCH_PRICE = Object.fromEntries(BRUNCH_CARTA.map(([, i, p]) => [i, p]));
const MIN_MENU = 45;
const isBrunch = q => q.termsKey === "brunch" || /brunch/i.test(q.oggetto || "");
const brunchSum = q => q.sezioni.reduce((a, s) => a + s.items.reduce((b, i) => b + (BRUNCH_PRICE[i.trim()] || 0), 0), 0);
function brunchGroups(){
  const cats = [...new Set(BRUNCH_CARTA.map(r => r[0]))];
  return cats.map(c => `<optgroup label="${esc("Brunch alla carta · " + c)}">${BRUNCH_CARTA.filter(r => r[0] === c).map(([, i, p]) => `<option value="${esc(i)}">${esc(i)} · ${eurPlain(p)} €</option>`).join("")}</optgroup>`).join("");
}
function optGroups(key){
  const merged = {};
  // Nei preventivi di Natale si sceglie solo dal menu di Natale
  Object.entries(CATALOG[key]).filter(([g]) => isNataleAz(Q) ? g === NATALE : g !== NATALE).forEach(([g, arr]) => { const b = bandOf(g); merged[b] = merged[b] || []; arr.forEach(i => { if (!merged[b].includes(i)) merged[b].push(i); }); });
  return Object.entries(merged).map(([g, arr]) => `<optgroup label="${esc(g)}">${arr.map(i => `<option value="${esc(i)}">${esc(i)}${VEG.has(i) ? " 🌿" : ""}${SUPPL[i] ? ` (+${SUPPL[i]} €)` : ""}</option>`).join("")}</optgroup>`).join("");
}

function renderEditor(){
  const c = compute(Q);
  const natale = isNataleAz(Q);
  const sectHTML = Q.sezioni.map((s, si) => natale && !s.items.length && !CATALOG[s.key][NATALE] && s.key !== "dessert" ? "" : `
    <div class="sect ${s.items.length ? "" : "empty"}">
      <div class="sect-head">
        <input class="lbl" id="sl_${si}" aria-label="Nome portata" data-p="sezioni.${si}.label" value="${esc(s.label)}">
        <input id="ss_${si}" aria-label="Nota in rosso sotto il nome" data-p="sezioni.${si}.sub" value="${esc(s.sub.replace(/\n/g, " / "))}" placeholder="Nota rossa (es. Buffet / 3 scelte possibili)">
        <span class="count">${s.items.length}</span>
      </div>
      <div class="items">
        ${s.items.map((it, ii) => `<div class="item">
          <input id="si_${si}_${ii}" aria-label="Piatto" data-p="sezioni.${si}.items.${ii}" value="${esc(it)}">
          ${SUPPL[it.trim()] ? `<label class="check suppl" title="Il cliente ha scelto questo piatto: aggiunge ${SUPPL[it.trim()]} € a persona"><input type="checkbox" data-suppl="${esc(it.trim())}" ${(Q.supplScelti || []).includes(it.trim()) ? "checked" : ""}> scelto +${SUPPL[it.trim()]} €</label>` : ""}
          <button type="button" class="ghost" data-act="up" data-s="${si}" data-i="${ii}" aria-label="Sposta su" ${ii ? "" : "disabled"}>↑</button>
          <button type="button" class="ghost" data-act="rm" data-s="${si}" data-i="${ii}" aria-label="Rimuovi">✕</button>
        </div>`).join("")}
      </div>
      <div class="row">
        <select class="grow" id="pick_${si}" aria-label="Aggiungi piatto dal menu"><option value="">+ Aggiungi dal menu…</option>${!natale && (isBrunch(Q) || /Buffet$/.test(s.key)) ? brunchGroups() : ""}${optGroups(s.key)}<option value="__free">Scrivi un piatto nuovo…</option></select>
      </div>
    </div>`).join("");

  const wineBlock = (k, title, list) => {
    const w = Q[k]; const cc = c.d[k]; const beer = k === "birra";
    const unit = beer ? "Fusti" : (k === "vinoCasa" ? "Litri/bott." : "Bottiglie");
    return `<div class="sect">
      <div class="row" style="flex-wrap:wrap">
        <label class="check"><input type="checkbox" id="${k}_on" data-p="${k}.on" ${w.on ? "checked" : ""}> <b>${title}</b></label>
        <span class="grow"></span>
        <label class="check"><input type="checkbox" id="${k}_inc" data-p="${k}.incluso" ${w.incluso ? "checked" : ""}> incluso nel prezzo</label>
      </div>
      ${w.on ? `
      <div class="row">
        <label class="f grow" for="${k}_t">Titolo nel PDF<input id="${k}_t" data-p="${k}.titolo" value="${esc(w.titolo || "")}" placeholder="${esc(L[Q.lang || "it"][k])}"></label>
        <label class="f" for="${k}_pr" style="width:96px">€ ${beer ? "a fusto" : "a bottiglia"}<input id="${k}_pr" inputmode="decimal" data-p="${k}.prezzo" value="${esc(w.prezzo)}"></label>
      </div>
      <div class="wine-row wine-head"><span>${beer ? "Birra" : "Vino"}</span><span>${unit}</span><span>${beer ? "Pinte" : "Calici"}</span><span></span></div>
      ${w.righe.map((r, ri) => `<div class="wine-row">
        <input id="${k}_n_${ri}" aria-label="${beer ? "Birra" : "Vino"}" list="${list}" data-p="${k}.righe.${ri}.nome" value="${esc(r.nome)}">
        <input id="${k}_b_${ri}" aria-label="${unit}" inputmode="decimal" data-p="${k}.righe.${ri}.bott" value="${esc(r.bott)}">
        <input id="${k}_c_${ri}" aria-label="${beer ? "Pinte" : "Calici"}" inputmode="numeric" data-p="${k}.righe.${ri}.calici" value="${esc(r.calici)}" placeholder="${Math.round(num(r.bott) * perBottle(k, r.nome))}">
        <button type="button" class="ghost" data-act="wrm" data-w="${k}" data-i="${ri}" aria-label="Rimuovi riga">✕</button>
      </div>`).join("")}
      <div class="row"><button type="button" data-act="wadd" data-w="${k}">+ ${beer ? "Birra" : "Vino"}</button><span class="hint grow" style="text-align:right">${cc.bott} ${beer ? "fusti" : "bott."} · ${cc.cal} ${beer ? "pinte" : "calici"} · ${w.incluso ? "incluso" : eur(cc.prezzo)}</span></div>` : ""}
    </div>`;
  };

  const archive = store.quotes.slice().sort((a, b) => b.updated - a.updated).map(q => `
    <div class="arch-item ${q.id === Q.id ? "cur" : ""}">
      <div class="meta" data-act="open" data-id="${q.id}" role="button" tabindex="0"><b>${esc(q.cliente || "Senza nome")}</b><span>${esc(q.oggetto)} · ${q.dataEvento ? dateIT(q.dataEvento) : "data da definire"} · ${esc(q.numero)}</span></div>
      ${pendingDelete === q.id
        ? `<button type="button" class="danger" data-act="delyes" data-id="${q.id}">Elimina</button><button type="button" class="ghost" data-act="delno">Annulla</button>`
        : `<button type="button" class="ghost" data-act="del" data-id="${q.id}" aria-label="Elimina preventivo">✕</button>`}
    </div>`).join("");

  const two = Q.opzioni.length > 1;
  editor.innerHTML = `
    <div class="card">
      <h2>Cliente ed evento</h2>
      <div class="grid">
        ${field("Cliente", "cliente", { ph: "Nome e cognome" })}
        ${field("Email", "email", { type: "email" })}
        ${field("Telefono", "telefono", { type: "tel", ph: "es. +39 333 123 4567" })}
        <label class="f" for="f_oggettoSel">Tipo di evento<select id="f_oggettoSel">${EVENT_TYPES.map(e => `<option ${!customEvent() && Q.oggetto === e ? "selected" : ""}>${esc(e)}</option>`).join("")}<option value="__altro" ${customEvent() ? "selected" : ""}>Altro (scrivi tu)…</option></select></label>
        ${customEvent() ? field("Scrivi il tipo di evento", "oggetto", { ph: "es. Festa dei nonni" }) : ""}
        ${field("Data evento", "dataEvento", { type: "date" })}
        <label class="f" for="f_oraInizio">Inizio evento<select id="f_oraInizio" data-p="oraInizio"><option value="">Non indicato</option>${START_TIMES.map(t => `<option ${Q.oraInizio === t ? "selected" : ""}>${t}</option>`).join("")}</select></label>
        ${field("Orario / nota sotto la data", "orario", { ph: "es. Ore 12:00 o Pranzo" })}
        <label class="f" for="f_numero">Numero preventivo<div class="row"><input id="f_numero" class="grow" data-p="numero" value="${esc(Q.numero)}"><button type="button" class="ghost" data-act="renum" aria-label="Genera nuovo numero" title="Nuovo numero">↻</button></div></label>
        <label class="f" for="f_letters">Lettere del numero (per i nuovi preventivi)<input id="f_letters" maxlength="2" value="${esc(getLetters())}" style="text-transform:uppercase"></label>
        ${field("Data preventivo", "data", { type: "date" })}
        <label class="f" for="f_lang">Lingua del preventivo<select id="f_lang"><option value="it" ${Q.lang !== "en" ? "selected" : ""}>Italiano</option><option value="en" ${Q.lang === "en" ? "selected" : ""}>English</option></select></label>
      </div>
    </div>

    <div class="card">
      <h2>Ospiti e prezzo a persona</h2>
      <div class="grid three">
        ${field("Adulti", "adulti", { type: "number" })}
        ${field("Bambini", "bambini", { type: "number" })}
        ${field("€ per bambino", "prezzoBambino", { type: "number" })}
      </div>
      ${Q.opzioni.map((o, oi) => `<div class="row">
        <label class="f" for="op_p_${oi}" style="width:110px">Opzione ${two ? oi+1 : ""} €<input id="op_p_${oi}" inputmode="decimal" data-p="opzioni.${oi}.prezzo" value="${esc(o.prezzo)}"></label>
        <label class="f grow" for="op_l_${oi}">Descrizione ${two ? "" : "(facoltativa)"}<input id="op_l_${oi}" data-p="opzioni.${oi}.label" value="${esc(o.label)}" placeholder="${two ? "es. Solo aperitivo" : ""}"></label>
        ${oi ? `<button type="button" class="ghost" data-act="oprm" data-i="${oi}" aria-label="Rimuovi opzione" style="align-self:end">✕</button>` : ""}
      </div>`).join("")}
      ${!isBrunch(Q) && Q.opzioni.some(o => num(o.prezzo) < MIN_MENU) ? `<div class="warn">I menu partono da un minimo di ${MIN_MENU} € a persona (solo il brunch può costare meno).</div>` : ""}
      ${two ? "" : `<div><button type="button" data-act="opadd">+ Seconda opzione di prezzo</button></div>`}
      ${brunchSum(Q) ? `<div class="row"><span class="hint grow">Somma delle voci del brunch alla carta: <b>${eurPlain(brunchSum(Q))} €</b> a persona</span><button type="button" data-act="brunchsum">Usa come prezzo</button></div>` : ""}
      <div class="hint">Menu da ${MIN_MENU} € in su: 45 · 50 · 55 · 60 · 65 · 70 · 75 €. Brunch a buffet 25-30 €, oppure alla carta con i prezzi del menu brunch. Bambini fino a 10 anni 15 €.</div>
    </div>

    <div class="card">
      <h2>Menu</h2>
      ${sectHTML}
    </div>

    <div class="card">
      <h2><label class="check" style="text-transform:none;letter-spacing:0;font-size:13px;color:var(--ink)"><input type="checkbox" id="torta_on" data-p="torta.on" ${Q.torta.on ? "checked" : ""}> Torta dal partner di pasticceria</label></h2>
      ${Q.torta.on ? `<div class="grid three">
        ${field("Grammi a persona", "torta.grammi", { type: "number" })}
        ${field("€ al kg", "torta.euroKg", { type: "number" })}
        <div class="f">Totale<div style="padding:8px 0;font-weight:700">${kgFmt(c.kg)} kg · ${eur(c.torta)}</div></div>
      </div>` : ""}
    </div>

    <div class="card">
      <h2>Vini</h2>
      ${wineBlock("vinoCasa", "Vino della casa", "dl_casa")}
      ${wineBlock("vinoBott", "Vino bottiglia", "dl_bott")}
      ${wineBlock("birra", "Birra alla spina", "dl_birra")}
      <div class="hint">Calici se lasci il campo vuoto: 10 per litro di vino della casa, 10 per le bottiglie Blazevi da 1 litro, 7,5 per le bottiglie da 0,75; 60 pinte per fusto da 30 litri. Scrivi un numero per cambiarlo.</div>
    </div>

    <div class="card">
      <h2>Totali</h2>
      <div class="grid">
        ${field("Nota sotto i totali", "nota", { ph: "es. 3 persone intolleranti al lattosio", full: true })}
        <label class="check full"><input type="checkbox" id="f_accNoTorta" data-p="accontoNoTorta" ${Q.accontoNoTorta ? "checked" : ""}> Calcola l'acconto del 50% sul prezzo senza torta</label>
      </div>
      <div class="totals" id="edTotals"></div>
    </div>

    <div class="card">
      <h2>Termini & condizioni<span class="spacer"></span>
        <select id="termsKey" style="width:auto;text-transform:none;letter-spacing:0;font-weight:500">${Object.entries(TERMS).map(([k, t]) => `<option value="${k}" ${Q.termsKey === k ? "selected" : ""}>${t.name}</option>`).join("")}</select>
      </h2>
      ${field("Condizioni (un paragrafo per riga vuota; il primo esce in grassetto)", "terms", { textarea: true, rows: 9, full: true })}
      ${field("Allergie e preferenze alimentari", "allergie", { textarea: true, rows: 6, full: true })}
      ${field("Paragrafi finali (facoltativi)", "termsAfter", { textarea: true, rows: 3, full: true })}
    </div>

    <div class="card">
      <h2>Preventivi salvati<span class="spacer"></span><span style="text-transform:none;letter-spacing:0;font-weight:500">${store.quotes.length}</span></h2>
      <div class="archive">${archive}</div>
      <div class="hint">${SERVER ? "Salvati in automatico sul server e condivisi con tutti i dipendenti." : "Salvati in automatico in questo browser. Usa Esporta per una copia di sicurezza o per spostarli su un altro computer."} <span class="sync" id="sync"></span></div>
      <div class="row"><button type="button" data-act="export">Esporta archivio</button><label class="btn" for="importFile" style="font-weight:600">Importa<input type="file" id="importFile" accept=".json,application/json" hidden></label></div>
    </div>

    <datalist id="dl_casa">${WINES_CASA.map(e => `<option value="${esc(e)}">`).join("")}</datalist>
    <datalist id="dl_birra">${BEERS.map(e => `<option value="${esc(e)}">`).join("")}</datalist>
    <datalist id="dl_bott">${WINES_BOTT.map(e => `<option value="${esc(e)}">`).join("")}</datalist>
  `;
  renderTotalsBox(c);
  $("#curName").textContent = Q.cliente ? `${Q.cliente} · ${Q.numero}` : Q.numero;
}

function totalRows(q, c){
  const T = L[q.lang || "it"];
  const rows = [];
  const two = q.opzioni.length > 1;
  if (two) {
    q.opzioni.forEach((o, i) => rows.push([T.foodOpt(lc(o.label) || T.opz(i+1)), c.food[i]]));
    if (c.sup) rows.push([T.suppl(c.supp), c.sup]);
    if (c.torta) rows.push([T.torta, c.torta]);
    if (c.vini > 0) rows.push([T.vini, c.vini]);
    q.opzioni.forEach((o, i) => rows.push([T.finaleOpt(lc(o.label) || T.opz(i+1)), c.finale[i]]));
  } else if (c.vini > 0) {
    rows.push([T.food, c.food[0]]);
    if (c.sup) rows.push([T.suppl(c.supp), c.sup]);
    rows.push([T.vini, c.vini]);
    rows.push([c.torta ? T.finaleTorta : T.totale, c.finale[0]]);
  } else {
    if (c.sup) { rows.push([T.food, c.food[0]]); rows.push([T.suppl(c.supp), c.sup]); }
    rows.push([T.finale, r2(c.finale[0] - c.torta)]);
    if (c.torta) rows.push([T.conTorta, c.finale[0]]);
  }
  if (!two) rows.push([T.acconto, c.acconto, "acc"]);
  return rows;
}
function renderTotalsBox(c){
  const box = $("#edTotals"); if (!box) return;
  const rows = totalRows(Q, c);
  box.innerHTML = rows.map(([l, v, k]) => `<div class="${k === "acc" ? "acc" : (/finale|Totale|Final|Total/.test(l) ? "strong" : "")}"><span>${esc(l)}</span><span>${eur(v)}</span></div>`).join("");
  $("#pvTotal").textContent = eur(c.finale[0]) + (Q.opzioni.length > 1 ? " / " + eur(c.finale[1]) : "");
}

/* ================= Document ================= */
function drinkTable(q, k, cc, T){
  const w = q[k];
  if (!w.on || !w.righe.length) return "";
  const unit = k === "birra" ? T.fusti : (k === "vinoCasa" && w.righe.some(r => /sfus|fuso|tap|spina/i.test(r.nome)) ? T.litri : T.bottiglie);
  const glass = k === "birra" ? T.pinte : T.calici;
  const rows = w.righe.map(r => `<tr><td style="width:250px;white-space:nowrap">${esc(r.nome)}</td><td class="c" style="width:70px">${esc(num(r.bott))}</td><td class="c" style="width:90px">${calici(r, k)}</td></tr>`).join("");
  return `<table class="tbl">
    <tr class="h"><td></td><td class="c">${unit}</td><td class="c">${glass}</td></tr>
    ${rows}
    <tr class="t"><td></td><td class="c">${cc.bott}</td><td class="c">${cc.cal}</td></tr>
    ${w.incluso ? "" : `<tr class="p"><td></td><td class="c">€</td><td class="c">${cc.prezzo ? eurPlain(cc.prezzo) : "-"}</td></tr>`}
  </table>`;
}
function renderDoc(q){
  const c = compute(q);
  const T = L[q.lang || "it"];
  const secHtml = s => `
    <div class="d-sec"><div class="k">${esc(s.label.replace(/:\s*$/, ""))}</div>${s.sub ? `<div class="d-sub">${esc(s.sub)}</div>` : ""}
    <div class="d-items">${s.items.filter(i => i.trim()).map(i => `<div>${esc(i)}${VEG.has(i.trim()) ? LEAF : ""}${SUPPL[i.trim()] ? `<span class="d-suppl">+${SUPPL[i.trim()]} €</span>` : ""}</div>`).join("")}</div></div>`;
  // Primi and Secondi sit side by side; every other course runs the full width
  const used = q.sezioni.filter(s => s.items.some(i => i.trim()));
  const pair = ["primi", "secondi"].map(k => used.find(s => s.key === k));
  const secs = used.map(s => {
    if (pair[0] && pair[1] && s.key === "primi") return `<div class="d-pair">${secHtml(pair[0])}${secHtml(pair[1])}</div>`;
    if (pair[0] && pair[1] && s.key === "secondi") return "";
    return secHtml(s);
  }).join("");
  const opts = q.opzioni.map(o => `<div class="d-opt"><span>${T.opzione}</span><span class="p">${eur(num(o.prezzo))}</span>${o.label ? `<span class="l">${esc(o.label)}</span>` : ""}</div>`).join("");
  const cake = q.torta.on ? `<div class="d-cake d-sec"><div class="k">${T.torte.replace(/:\s*$/, "")}</div><div style="display:flex;flex-direction:column;align-items:center">
      <div class="txt">${T.torteTxt(eurPlain(num(q.torta.euroKg)).replace(",00", ""))}</div>
      <table class="tbl" style="margin-top:10px">
        <tr class="lone"><td style="width:250px">${esc(T.grammi(num(q.torta.grammi)))}</td><td class="c" style="width:70px">${kgFmt(c.kg)} kg</td><td style="width:90px"></td></tr>
        <tr class="t"><td>${T.prezzo}</td><td class="c">${eur(c.torta)}</td><td></td></tr>
      </table>
    </div></div>` : "";
  const drinks = DRINKS.map(k => {
    const t = drinkTable(q, k, c.d[k], T); if (!t) return "";
    const loose = q[k].righe.some(r => /sfus|fuso|tap|spina/i.test(r.nome));
    const note = k === "vinoCasa" && !loose ? `<div class="d-wnote">${T.litro1}</div>` : "";
    return `<div class="d-wine"><div class="k">${esc((q[k].titolo || T[k]).replace(/:\s*$/, ""))}</div>${note}${t}</div>`;
  }).join("");
  const tot = totalRows(q, c).map(([l, v, k]) => `<tr class="${k === "acc" ? "acc" : (/finale|Totale|Final|Total/.test(l) ? "strong" : "")}"><td class="l">${esc(l)}</td><td class="r">${eurPlain(v)} €</td></tr>`).join("");
  const paras = t => (t || "").split(/\n\s*\n/).map(p => p.trim()).filter(Boolean);
  const terms = paras(q.terms).map((p, i) => `<p class="${i === 0 ? "bold" : ""}">${esc(p)}</p>`).join("");
  const allergie = paras(q.allergie).map(p => `<p>${esc(p)}</p>`).join("");
  const after = paras(q.termsAfter).map(p => `<p>${esc(p)}</p>`).join("");

  return `
  <div class="page p1">
    <div class="frame" aria-hidden="true"><i class="c tl"></i><i class="c tr"></i><i class="c bl"></i><i class="c br"></i><i class="b"></i></div>
    <div class="d-logo-row">
      <div class="d-brand"><img class="d-logo" src="${LOGO}" alt="DVOR"><div class="d-venue"><span>Via Castello 5</span><span>San Floriano del Collio</span><span>37070 Gorizia (Italia)</span></div></div>
      <div class="d-addr d-company"><b>Horeca FVG Srl</b><span>P.IVA 01280650316</span><span>info@osteriadvor.com</span></div>
    </div>
    ${isNatale(q) ? `<div class="d-holly">${HOLLY}</div>` : `<div class="d-rule" aria-hidden="true"></div>`}
    <div class="d-head">
      <div class="d-client"><span class="k2">${T.cliente.replace(/:\s*$/, "")}</span><b>${esc(q.cliente) || "&nbsp;"}</b>${q.email ? `<div>${esc(q.email)}</div>` : ""}${q.telefono ? `<div>${T.tel} ${esc(q.telefono)}</div>` : ""}</div>
      <div class="d-meta"><span class="k">${T.numero}</span><span class="v">${esc(q.numero)}</span><span class="k">${T.data.replace(/:\s*$/, "")}</span><span class="v">${T.date(q.data)}</span></div>
    </div>
    <div class="d-obj"><div><span class="k2">${T.oggetto}</span><b>${esc(q.oggetto)}</b></div>
      <div class="ev"><span class="k2">${T.dataEvento.replace(/:\s*$/, "")}</span><span class="v">${T.date(q.dataEvento)}${q.oraInizio ? `<br>${T.inizio} ${esc(q.oraInizio)}` : ""}${q.orario ? `<br>${esc(q.orario)}` : ""}</span></div></div>
    <div class="d-qty"><span>${T.adulti.replace(/:\s*$/, "")}: <b>${esc(num(q.adulti))}</b></span>${num(q.bambini) || q.opzioni.length > 1 ? `<span>${T.bambini.replace(/:\s*$/, "")}: <b>${esc(num(q.bambini))}</b></span>` : ""}</div>
    <div class="d-opts">${opts}</div>
    <div class="d-menu">${secs}</div>
    ${used.some(s => s.items.some(i => VEG.has(i.trim()) || SUPPL[i.trim()])) ? `<div class="d-legend">${used.some(s => s.items.some(i => VEG.has(i.trim()))) ? `<span>${LEAF} ${q.lang === "en" ? "Vegetarian dish" : "Piatto vegetariano"}</span>` : ""}${used.some(s => s.items.some(i => SUPPL[i.trim()])) ? `<span><b class="d-suppl" style="margin:0 3px 0 0">+5 €</b>${T.supplNota}</span>` : ""}</div>` : ""}
    ${cake}
    <div style="margin-top:${q.torta.on ? 8 : 26}px">${drinks}</div>
    <table class="tbl d-tot">${tot}</table>
    ${q.nota ? `<div class="d-note">${esc(q.nota)}</div>` : ""}
  </div>
  <div class="page p2 fixed">
    <div class="frame" aria-hidden="true"><i class="c tl"></i><i class="c tr"></i><i class="c bl"></i><i class="c br"></i><i class="b"></i></div>
    <h3>${T.termini}</h3>
    <div class="terms">${terms}</div>
    <div class="terms allergy"><h3 style="margin:0 0 7px">${T.allergie}</h3>${allergie}${after}</div>
    ${isNatale(q) ? `<div class="d-xmas">${q.lang === "en" ? "Season's Greetings from all of us at Osteria Dvor" : "Con i migliori auguri di Buone Feste da tutta l'Osteria Dvor"}</div>` : ""}
    <div class="sign"><div>Horeca FVG Srl<img src="${SIG}" alt="Firma Horeca FVG Srl"></div><div class="c">${T.firmaCliente}</div></div>
  </div>`;
}

const pagesEl = $("#pages"), scaler = $("#scaler"), viewport = $("#viewport");
function renderPreview(){
  pagesEl.innerHTML = renderDoc(Q);
  const p1 = pagesEl.querySelector(".p1");
  fitPage(p1);
  fitPreview();
  $("#pvWarn").hidden = !(p1 && p1.scrollHeight > 1124);
}
function fitPage(p1){
  if (!p1) return;
  if (p1.scrollHeight > 1124) p1.classList.add("compact");
  if (p1.scrollHeight > 1124) p1.classList.add("compact2");
  if (p1.scrollHeight > 1124) p1.classList.add("compact3");
}
function fitPreview(){
  const w = viewport.clientWidth;
  const s = Math.min(1, w / 794);
  scaler.style.transform = `scale(${s})`;
  scaler.style.height = (pagesEl.offsetHeight * s) + "px";
  scaler.style.width = (794 * s) + "px";
}
new ResizeObserver(fitPreview).observe(viewport);

/* ================= Updates ================= */
let saveT, pvT;
function touch(){ Q.updated = Date.now(); clearTimeout(saveT); saveT = setTimeout(saveAll, 300); }
function refreshLight(){ clearTimeout(pvT); pvT = setTimeout(() => { renderPreview(); renderTotalsBox(compute(Q)); }, 120); touch(); }
function refreshAll(){ renderEditor(); renderPreview(); touch(); }

const NUMERIC = /^(adulti|bambini|prezzoBambino|opzioni\.\d+\.prezzo|torta\.(grammi|euroKg)|(vino\w+|birra)\.prezzo|(vino\w+|birra)\.righe\.\d+\.bott)$/;
editor.addEventListener("input", e => {
  const t = e.target, p = t.dataset.p; if (!p) return;
  if (t.type === "checkbox") return;
  let v = t.value;
  if (/^sezioni\.\d+\.sub$/.test(p)) v = v.split(/\s*\/\s*/).join("\n");
  if (NUMERIC.test(p)) v = v.replace(",", ".");
  setPath(Q, p, v);
  if (/^(vino\w+|birra)\.righe\.\d+\.(bott|nome)$/.test(p)) { const k = p.split(".")[0]; const row = getPath(Q, p.replace(/\.(bott|nome)$/, "")); const ci = t.parentElement.querySelector('[data-p$=".calici"]'); if (ci) ci.placeholder = Math.round(num(row.bott) * perBottle(k, row.nome)); }
  if (p === "data") { const m = NUM_RE.exec(Q.numero || ""); if (m && /^\d{4}-\d{2}-\d{2}$/.test(v)) { Q.numero = numDate(v) + m[2] + m[3]; const f = $("#f_numero"); if (f) f.value = Q.numero; } }
  if (p === "cliente" || p === "numero" || p === "data") $("#curName").textContent = (Q.cliente ? Q.cliente + " · " : "") + Q.numero;
  refreshLight();
});
editor.addEventListener("change", e => {
  const t = e.target;
  if (t.dataset.suppl) { const n = t.dataset.suppl; Q.supplScelti = (Q.supplScelti || []).filter(x => x !== n); if (t.checked) Q.supplScelti.push(n); refreshAll(); return; }
  if (t.type === "checkbox" && t.dataset.p) { setPath(Q, t.dataset.p, t.checked); refreshAll(); return; }
  if (t.id === "termsKey") { useTerms(Q, t.value); refreshAll(); return; }
  if (t.id === "f_letters") { setLetters(t.value); return; }
  if (t.id === "f_lang") { setLang(Q, t.value); refreshAll(); return; }
  if (t.id === "f_oggettoSel") {
    if (t.value === "__altro") { Q.oggettoLibero = true; Q.oggetto = ""; refreshAll(); const inp = $("#f_oggetto"); inp && inp.focus(); }
    else { Q.oggettoLibero = false; Q.oggetto = t.value; refreshAll(); }
    return;
  }
  if (t.id === "importFile") { importFile(t.files[0]); return; }
  if (t.id && t.id.startsWith("pick_")) {
    const si = +t.id.slice(5); let v = t.value; t.value = "";
    if (!v) return;
    Q.sezioni[si].items.push(v === "__free" ? "" : v);
    refreshAll();
    if (v === "__free") { const inp = document.getElementById(`si_${si}_${Q.sezioni[si].items.length-1}`); inp && inp.focus(); }
    return;
  }
  if (t.dataset.p && /^sezioni\.\d+\.items\.\d+$/.test(t.dataset.p) && !t.value.trim()) {
    // blank item left behind: drop it
    const [, si, , ii] = t.dataset.p.split("."); Q.sezioni[si].items.splice(+ii, 1); refreshAll();
  }
});
let pendingDelete = null;
editor.addEventListener("click", e => {
  const b = e.target.closest("[data-act]"); if (!b) return;
  const a = b.dataset.act, si = +b.dataset.s, ii = +b.dataset.i;
  if (a === "rm") Q.sezioni[si].items.splice(ii, 1);
  else if (a === "up") { const it = Q.sezioni[si].items; [it[ii-1], it[ii]] = [it[ii], it[ii-1]]; }
  else if (a === "wadd") Q[b.dataset.w].righe.push({ nome: "", bott: 1, calici: "" });
  else if (a === "wrm") Q[b.dataset.w].righe.splice(ii, 1);
  else if (a === "opadd") { Q.opzioni[0].label = Q.opzioni[0].label || "Opzione 1"; Q.opzioni.push({ prezzo: num(Q.opzioni[0].prezzo) + 10, label: "Opzione 2" }); }
  else if (a === "oprm") Q.opzioni.splice(ii, 1);
  else if (a === "brunchsum") Q.opzioni[0].prezzo = Math.round(brunchSum(Q) * 100) / 100;
  else if (a === "renum") { assignNumber(Q).then(() => { if (!SERVER) refreshAll(); }); return; }
  else if (a === "open") { Q = store.quotes.find(q => q.id === b.dataset.id) || Q; store.current = Q.id; pendingDelete = null; saveAll(); renderEditor(); renderPreview(); window.scrollTo({ top: 0 }); return; }
  else if (a === "del") { pendingDelete = b.dataset.id; renderEditor(); return; }
  else if (a === "delno") { pendingDelete = null; renderEditor(); return; }
  else if (a === "delyes") { removeQuote(b.dataset.id); return; }
  else if (a === "export") { exportArchive(); return; }
  else return;
  refreshAll();
});
editor.addEventListener("keydown", e => { if ((e.key === "Enter" || e.key === " ") && e.target.matches(".meta[data-act]")) { e.preventDefault(); e.target.click(); } });

function useQuote(q){ if (!q.numero) { if (SERVER) assignNumber(q); else q.numero = makeNumber(q.data, getLetters(), localSeq()); } store.quotes.push(q); store.current = q.id; Q = q; pendingDelete = null; saveAll(); renderEditor(); renderPreview(); window.scrollTo({ top: 0 }); }
function removeQuote(id){
  store.quotes = store.quotes.filter(q => q.id !== id); pendingDelete = null;
  if (!store.quotes.length) { const b = blankQuote(); store.quotes.push(b); assignNumber(b); }
  if (Q.id === id) Q = store.quotes[0];
  if (SERVER) { toPush.delete(id); api("quote&id=" + encodeURIComponent(id), { method: "DELETE" }).catch(err => toast("Non eliminato dal server: " + err.message)); }
  store.current = Q.id; saveAll(); renderEditor(); renderPreview(); toast("Preventivo eliminato");
}
$("#btnNew").onclick = () => useQuote(blankQuote());
$("#btnDup").onclick = () => { const q = JSON.parse(JSON.stringify(Q)); q.id = uid(); q.numero = ""; q.data = todayISO(); q.updated = Date.now(); useQuote(q); toast("Copia creata con un nuovo numero"); };
const presetSel = $("#preset");
[...new Set(PRESETS.map(p => p.group || "Altri esempi"))].forEach(g => presetSel.insertAdjacentHTML("beforeend", `<optgroup label="${esc(g)}">${PRESETS.map((p, i) => (p.group || "Altri esempi") === g ? `<option value="${i}">${esc(p.name)}</option>` : "").join("")}</optgroup>`));
presetSel.onchange = () => { const i = presetSel.value; presetSel.value = ""; if (i === "") return; useQuote(PRESETS[+i].make()); toast("Nuovo preventivo creato dall'esempio"); };

function toast(msg){ const t = $("#toast"); t.textContent = msg; t.hidden = false; clearTimeout(toast.t); toast.t = setTimeout(() => t.hidden = true, 2600); }

/* ================= Files ================= */
async function offerFile(filename, data){
  let dl = null;
  try { dl = window.claude && window.claude.use ? await window.claude.use("downloads") : null; } catch { dl = null; }
  if (dl) {
    try { await dl.save({ filename, data }); toast("File salvato"); return; }
    catch (err) { if (err && err.code === "declined") return; if (err && err.code === "rate_limited") { toast("Attendi un attimo e riprova"); return; } }
  }
  if (!window.claude) { // opened as a local file: a normal browser download works
    const url = URL.createObjectURL(data instanceof Blob ? data : new Blob([data]));
    const a = document.createElement("a"); a.href = url; a.download = filename; document.body.appendChild(a); a.click(); a.remove();
    setTimeout(() => URL.revokeObjectURL(url), 4000); return;
  }
  toast("Il salvataggio dei file non è disponibile in questa vista");
}
function fileBase(q){
  const name = (q.cliente || "Preventivo").normalize("NFD").replace(/[̀-ͯ]/g, "").replace(/[^A-Za-z0-9]+/g, "_").replace(/^_|_$/g, "");
  const d = q.dataEvento ? q.dataEvento.split("-").reverse().join("_") : q.numero;
  return `${name}_${d}`;
}
async function exportArchive(){
  await offerFile(`Preventivi_Dvor_${todayISO()}.json`, new Blob([JSON.stringify(store, null, 1)], { type: "application/json" }));
}
function importFile(file){
  if (!file) return;
  file.text().then(txt => {
    const data = JSON.parse(txt);
    const list = Array.isArray(data) ? data : data.quotes;
    if (!Array.isArray(list)) throw new Error("bad");
    let added = 0;
    list.forEach(q => { if (q && q.id && Array.isArray(q.sezioni)) { migrate(q); const i = store.quotes.findIndex(x => x.id === q.id); if (i >= 0) store.quotes[i] = q; else store.quotes.push(q); added++; } });
    if (SERVER) list.forEach(q => { if (q && q.id && Array.isArray(q.sezioni)) pushQuote(q); });
    saveAll(); renderEditor(); toast(`${added} preventivi importati`);
  }).catch(() => toast("Il file non è un archivio di preventivi valido"));
}

$("#btnPdf").onclick = async () => {
  const btn = $("#btnPdf");
  if (!window.html2canvas || !window.jspdf) { toast("Componenti PDF non caricati: controlla la connessione e ricarica"); return; }
  btn.disabled = true; btn.textContent = "Creo il PDF…";
  const off = document.createElement("div"); off.className = "offscreen"; off.innerHTML = renderDoc(Q); document.body.appendChild(off); fitPage(off.querySelector(".p1"));
  try {
    if (document.fonts && document.fonts.ready) await document.fonts.ready;
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF({ unit: "mm", format: "a4", orientation: "portrait", compress: true });
    const pages = off.querySelectorAll(".page");
    for (let i = 0; i < pages.length; i++) {
      const canvas = await html2canvas(pages[i], { scale: 2, backgroundColor: "#ffffff", useCORS: true, logging: false });
      const img = canvas.toDataURL("image/jpeg", 0.92);
      const ratio = canvas.height / canvas.width;
      let w = 210, h = 210 * ratio;
      if (h > 297) { h = 297; w = 297 / ratio; }
      if (i) pdf.addPage();
      pdf.addImage(img, "JPEG", (210 - w) / 2, 0, w, h);
    }
    pdf.setProperties({ title: `Preventivo ${Q.numero} – ${Q.cliente}`, author: "Horeca FVG Srl – Osteria Dvor" });
    await offerFile(fileBase(Q) + ".pdf", pdf.output("blob"));
  } catch (err) {
    console.error(err); toast("Non sono riuscito a creare il PDF");
  } finally {
    off.remove(); btn.disabled = false; btn.textContent = "Scarica PDF";
  }
};

if (SERVER && window.DVOR_DATA && window.DVOR_DATA.user) {
  const u = window.DVOR_DATA.user, who = document.getElementById("who");
  who.innerHTML = `<span>${esc(u.name || u.username)}</span><a href="account.php">${u.role === "admin" ? "Utenti e account" : "Il mio account"}</a><button type="button" id="btnLogout" class="ghost">Esci</button>`;
  who.hidden = false;
  document.getElementById("btnLogout").onclick = async () => { try { await flushPush(); await api("logout", { method: "POST" }); } catch {} location.href = "login.php"; };
}
renderEditor();
renderPreview();
if (document.fonts && document.fonts.ready) document.fonts.ready.then(renderPreview);
</script>
</body></html>