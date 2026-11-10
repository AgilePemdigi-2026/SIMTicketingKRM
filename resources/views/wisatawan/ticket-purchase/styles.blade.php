<style>
@font-face{font-family:'Plus Jakarta Sans';src:url('{{ asset('fonts/PlusJakartaSans.ttf') }}') format('truetype');font-weight:200 800;font-display:swap}

.purchase-page{max-width:1040px;padding-block:30px 64px;font-size:14px;font-family:'Plus Jakarta Sans',sans-serif}
.purchase-page [hidden]{display:none!important}
.purchase-breadcrumb{display:flex;gap:10px;color:var(--color-muted);font-size:12px;margin-bottom:38px}
.purchase-breadcrumb a:hover{text-decoration:underline}
.purchase-steps{display:flex;justify-content:center;gap:68px;list-style:none;padding:0;margin:0 auto 46px}
.purchase-steps li{position:relative;display:flex;align-items:center;flex-direction:column;gap:10px;color:var(--color-muted);font-size:12px;text-align:center;flex:0 1 160px}
.purchase-steps li:not(:last-child):after{content:'';position:absolute;left:calc(50% + 26px);top:17px;width:calc(100% + 16px);border-top:1px solid var(--color-border)}
.purchase-steps li>span{display:grid;place-items:center;width:34px;height:34px;background:var(--color-surface);border:1px solid var(--color-border);font-weight:700}
.purchase-steps li[aria-current]>span,.purchase-steps li.completed>span{background:var(--color-primary);border-color:var(--color-primary);color:#fff}
.purchase-steps li[aria-current]{color:var(--color-primary);font-weight:700}
.purchase-heading{margin-bottom:24px}.purchase-heading h1{display:inline-block;font-size:26px;font-weight:700;margin:0 0 12px}.purchase-heading h1:after{content:'';display:block;background:var(--color-accent);height:3px;width:100%;margin-top:12px}
.purchase-heading p,.purchase-ticket-types p{color:var(--color-muted);line-height:1.7;margin-top:8px}
.purchase-ticket-types{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:24px}
.purchase-page h2{font-size:14px;font-weight:700;margin:0 0 18px}
.purchase-ticket-types h2{margin-bottom:0}.purchase-ticket-types .purchase-type[aria-pressed="true"]{border:1px solid var(--color-secondary);box-shadow:inset 3px 0 var(--color-secondary)}
.purchase-page .krm-card{border-radius:0}.purchase-fields{margin-bottom:20px}.purchase-fields>h2,.purchase-summary>h2{font-size:12px;letter-spacing:.08em;text-transform:uppercase;border-bottom:1px solid var(--color-border);padding-bottom:16px;margin-bottom:24px}.purchase-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.purchase-page label{display:flex;flex-direction:column;gap:8px;font-size:12px;color:var(--color-text)}
.purchase-page input,.purchase-page select{width:100%;border:1px solid var(--color-border);background:#fff;border-radius:2px;min-height:42px;padding:10px 12px;font:inherit;color:var(--color-text)}
.purchase-page :is(input,select,button,a):focus-visible{outline:2px solid var(--color-secondary);outline-offset:3px}
.purchase-page input[type=number]{max-width:110px}
.purchase-item{border-top:1px solid var(--color-border);padding-top:20px;margin-top:20px}.purchase-item-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}.purchase-item-heading h3{font-weight:700;font-size:13px}
.purchase-item .purchase-grid{grid-template-columns:1fr 1fr}.purchase-item label:first-child{grid-column:1/-1}
.purchase-item-details,.purchase-muted{font-size:12px;color:var(--color-muted);line-height:1.7;margin-top:12px}
.purchase-link{color:var(--color-primary);text-decoration:underline;text-underline-offset:4px;font-size:12px;background:none;border:0;padding:0;cursor:pointer}.purchase-link:disabled{opacity:.45;cursor:not-allowed}
#add-ticket{margin-top:22px}.purchase-page .krm-btn{font-size:12px;padding:10px 20px;min-height:42px;border-radius:2px}.purchase-page button:disabled{opacity:.55;cursor:wait}
.purchase-summary{margin-top:26px}.purchase-summary-row{display:flex;justify-content:space-between;gap:20px;padding:9px 0;font-size:13px;line-height:1.6}.purchase-summary-row span:last-child{white-space:nowrap}.purchase-total{display:flex;justify-content:space-between;gap:20px;border-top:1px solid var(--color-border);padding-top:18px;margin-top:12px}.purchase-total strong:last-child{color:var(--color-primary);font-size:20px}
.purchase-actions{display:flex;justify-content:flex-end;flex-wrap:wrap;gap:12px;margin-top:22px}.purchase-submit-note{text-align:right}.purchase-alert{background:#fff4ee;color:#8b3525;border:1px solid #ebc4b6;padding:18px;margin-bottom:24px;line-height:1.7}.purchase-alert ul{padding-left:20px;list-style:disc;margin-top:8px}
.purchase-success{border:1px solid var(--color-border);background:#eef4ee;padding:18px;margin-bottom:26px;line-height:1.7}.purchase-order-meta{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:26px}.purchase-order-meta dt{font-size:12px;color:var(--color-muted);margin-bottom:6px}.purchase-order-meta dd{overflow-wrap:anywhere}.purchase-status{display:inline-block;padding:6px 12px;background:#f8f0d9;color:#755316;font-size:12px}.purchase-order-item{border-top:1px solid var(--color-border);padding-top:18px;margin-top:18px}.purchase-order-item h3{font-weight:700;margin-bottom:8px}.purchase-visitors{margin-top:12px;line-height:1.8;color:var(--color-muted);font-size:12px}
@media(max-width:640px){.purchase-page{width:calc(100% - 32px);padding-top:22px}.purchase-steps{gap:10px;margin-bottom:32px}.purchase-steps li{font-size:10px}.purchase-steps li:not(:last-child):after{width:calc(100% - 42px)}.purchase-heading h1{font-size:23px}.purchase-grid,.purchase-ticket-types,.purchase-order-meta{grid-template-columns:1fr}.purchase-item .purchase-grid{grid-template-columns:1fr 1fr}.purchase-page .krm-card{padding:18px}.purchase-actions .krm-btn{flex:1}.purchase-summary-row{font-size:12px}}
.purchase-type{text-align:left;cursor:pointer;font:inherit;color:inherit;min-width:0}.purchase-type:hover{background:#f4f8f2}.purchase-type:disabled{cursor:default;opacity:.7}
.purchase-counter{display:flex;align-items:center;gap:8px}.purchase-counter button{border:1px solid var(--color-border);background:#fff;color:var(--color-primary);min-width:36px;min-height:42px;font-size:18px;cursor:pointer}.purchase-counter input{width:64px;text-align:center;appearance:textfield}.purchase-counter input::-webkit-inner-spin-button{appearance:none}
.purchase-checkbox{display:flex!important;flex-direction:row!important;align-items:center;margin-bottom:12px;font-size:13px!important}.purchase-checkbox input{width:18px!important;min-height:18px!important;accent-color:var(--color-primary)}
.purchase-field-error{color:#9c3023;font-size:12px;line-height:1.5}.purchase-field-error:empty{display:none}.purchase-page [aria-invalid="true"]{border-color:#b53829}
.purchase-page input[readonly]{background:#f1f5f0}.purchase-order-card,.purchase-empty{margin-top:20px}.purchase-order-card-header{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:22px}.purchase-order-card h2{overflow-wrap:anywhere;min-width:0}.purchase-order-card-header .purchase-status{flex-shrink:0}.purchase-pagination{margin-top:26px}.purchase-review-line{line-height:1.8;margin-bottom:12px;overflow-wrap:anywhere}.purchase-review-line h2{margin-bottom:8px}.purchase-summary-row span:first-child{min-width:0;overflow-wrap:anywhere}
@media(max-width:640px){.purchase-order-card-header{flex-direction:column}.purchase-summary-row{gap:10px}.purchase-counter{gap:5px}.purchase-counter button{min-width:30px}.purchase-counter input{width:46px}.purchase-steps li:not(:last-child):after{width:calc(100% - 32px)}}

.purchase-steps li.completed > span {
    position: relative;
    color: transparent;
}

.purchase-steps li.completed > span::after {
    content: "✓";
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    font-weight: 700;
    line-height: 1;
}

/* Sembunyikan judul Tiket 1 jika hanya ada satu jenis tiket */
#ticket-items > .purchase-item:only-child > .purchase-item-heading {
    display: none;
}

/* Layout Detail Pemilihan sesuai Figma */

/* Tanggal kunjungan memenuhi satu baris */
#primary-ticket-fields.purchase-primary-single > label:last-child {
    grid-column: 1 / -1;
}

/* Counter Dewasa dan Anak tetap berdampingan */
.purchase-item .purchase-grid.purchase-only-counters > label:first-child {
    grid-column: auto;
}

/* Hilangkan garis pemisah saat hanya ada satu jenis tiket */
#ticket-items > .purchase-item:only-child {
    border-top: none;
    padding-top: 0;
    margin-top: 18px;
}

/* Sementara nonaktifkan fitur tambah jenis tiket sesuai user story */
#add-ticket {
    display: none !important;
}
</style>
