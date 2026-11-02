<script>
(() => {
    const form = document.getElementById('ticket-purchase-form');
    if (!form) return;
    const catalog = {{ Illuminate\Support\Js::from($tickets) }};
    const oldItems = {{ Illuminate\Support\Js::from(old('items', [])) }};
    const serverErrors = {{ Illuminate\Support\Js::from($errors->messages()) }};
    const previewUrl = {{ Illuminate\Support\Js::from(route('ticket-purchase.preview')) }};
    const byId = id => document.getElementById(id);
    const type = byId('visitor-type');
    const visitDate = byId('visit-date');
    const sections = [null, byId('ticket-step'), byId('visitor-step'), byId('review-step')];
    const itemList = byId('ticket-items');
    const visitorList = byId('visitor-fields');
    const errorBox = byId('purchase-error');
    const next = byId('next-step');
    const previous = byId('previous-step');
    const submit = byId('submit-purchase');
    const add = byId('add-ticket');
    const bookerVisits = byId('booker-is-visitor');
    const money = new Intl.NumberFormat('id-ID', {style:'currency',currency:'IDR',minimumFractionDigits:0,maximumFractionDigits:2});
    let step = 1, busy = false, submitting = false, revision = 0, quote = null;
    let selectedGroup = 'masuk';
    form.noValidate = true;

    function el(tag, text, className) {
        const node = document.createElement(tag);
        if (text !== undefined) node.textContent = text;
        if (className) node.className = className;
        return node;
    }
    function cents(value) {
        const text = String(value ?? '');
        if (!/^\d+(\.\d{1,2})?$/.test(text)) return null;
        const [whole, fraction = ''] = text.split('.');
        const result = Number(whole)*100 + Number(fraction.padEnd(2,'0'));
        return Number.isSafeInteger(result) ? result : null;
    }
    function rate(ticket) {
        return ticket.rates.find(rate => rate.visitor_type === type.value && cents(rate.adult_price) !== null && cents(rate.child_price) !== null);
    }
    function available() { return catalog.filter(ticket => rate(ticket)); }
    function ticketFor(item) { return available().find(ticket => ticket.reference === item.reference); }
    function quantity(value) { return Math.min(4,Math.max(0,Number.parseInt(value,10)||0)); }
    function normalize(raw = {}) {
        const visitors = {}, counters = {adult:0,child:0};
        if (Array.isArray(raw.visitors)) raw.visitors.forEach(visitor => {
            if (!visitor || !Object.hasOwn(counters,visitor.age_category)) return;
            visitors[`${visitor.age_category}:${counters[visitor.age_category]++}`] = {
                name:typeof visitor.name === 'string' ? visitor.name : '',
                identity_number:typeof visitor.identity_number === 'string' ? visitor.identity_number : '',
            };
        });
        return {reference:typeof raw.ticket_reference === 'string' ? raw.ticket_reference : '',
            adult:quantity(raw.adult_quantity ?? 1),child:quantity(raw.child_quantity ?? 0),visitors};
    }
    const items = Array.isArray(oldItems) && oldItems.length ? oldItems.slice(0,4).map(raw=>normalize(raw||{})) : [normalize()];
    function changed() { revision++; quote = null; updateSummary(); }
    function dotName(name) { return name.replace(/\[/g,'.').replace(/\]/g,''); }
    function clearErrors() {
        errorBox.hidden = true;
        byId('server-errors')?.setAttribute('hidden','');
        form.querySelectorAll('[data-error]').forEach(node => node.textContent = '');
        form.querySelectorAll('[aria-invalid]').forEach(node => node.removeAttribute('aria-invalid'));
    }
    function errorTarget(wrapper,key) {
        const message = el('span',undefined,'purchase-field-error');
        message.dataset.error = key;
        message.id = `error-${key.replaceAll('.','-')}`;
        wrapper.append(message);
        return message;
    }
    function showErrors(errors) {
        byId('server-errors')?.setAttribute('hidden','');
        const list = el('ul');
        for (const [key,messages] of Object.entries(errors)) {
            const text = Array.isArray(messages) ? messages.join(' ') : messages;
            list.append(el('li',text));
            const field = Array.from(form.querySelectorAll('[name]')).find(input => input.type!=='hidden' && dotName(input.name) === key);
            const target = Array.from(form.querySelectorAll('[data-error]')).find(node => node.dataset.error === key);
            if (target) {
                target.textContent = text;
                if (field) {
                    target.id ||= `error-${key.replaceAll('.','-')}`;
                    field.setAttribute('aria-describedby',target.id);
                }
            }
            field?.setAttribute('aria-invalid','true');
        }
        errorBox.replaceChildren(el('strong','Periksa kembali data berikut:'),list);
        errorBox.hidden = false;
        errorBox.focus();
        return false;
    }
    function fail(message,key='items') { return showErrors({[key]:[message]}); }
    function validateSection(section) {
        const errors = {};
        section.querySelectorAll('input,select').forEach(input => {
            if (!input.disabled && !input.checkValidity()) {
                const label=input.getAttribute('aria-label') ?? input.closest('label')?.firstChild?.textContent?.trim() ?? 'Kolom';
                errors[dotName(input.name)] = input.validity.valueMissing ? `${label} wajib diisi.` : `Periksa format dan batas nilai ${label.toLowerCase()}.`;
            }
        });
        return Object.keys(errors).length ? showErrors(errors) : true;
    }
    function validateTickets() {
        if (!validateSection(sections[1])) return false;
        const count = items.reduce((sum,item)=>sum+item.adult+item.child,0);
        if (count<1 || count>4) return fail('Jumlah seluruh tiket harus 1 sampai 4 per transaksi.');
        if (items.some(item=>!ticketFor(item)||item.adult+item.child===0)) return fail('Pilih tiket yang tersedia dan isi minimal satu pengunjung pada setiap item.');
        return true;
    }
    function previewPayload() {
        return {visit_date:visitDate.value, visitor_type:type.value,
            items:items.map(item=>({ticket_reference:item.reference,adult_quantity:item.adult,child_quantity:item.child}))};
    }
    async function refreshQuote() {
        const version = revision;
        busy = true; updateSummary(); next.textContent = 'Memeriksa…';
        try {
            const response = await fetch(previewUrl, {
                method:'POST',credentials:'same-origin',
                headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':form.querySelector('[name="_token"]').value},
                body:JSON.stringify(previewPayload()),
            });
            const data = await response.json().catch(()=>null);
            if (!response.ok) {
                if (response.status===422 && data?.errors) showErrors(data.errors);
                else fail(response.status===419||response.status===401 ? 'Sesi berakhir. Muat ulang halaman lalu masuk kembali.' : 'Ketersediaan tiket belum dapat diperiksa. Silakan coba kembali.');
                return false;
            }
            if (version!==revision) return fail('Pilihan tiket berubah saat diperiksa. Tekan Lanjut lagi.');
            if (!data?.quote?.items || typeof data.quote.total !== 'string') return fail('Ringkasan harga belum dapat dimuat. Silakan coba kembali.');
            quote = data.quote;
            return true;
        } catch {
            return fail('Tidak dapat terhubung ke aplikasi. Periksa koneksi lalu coba lagi.');
        } finally {
            busy = false; updateSummary(); next.textContent = step===2 ? 'Tinjau Pesanan →' : 'Lanjut →';
        }
    }
    function updateSummary() {
        const summary = byId('summary-items');
        summary.replaceChildren();
        let total = 0;
        items.forEach((item,index)=>{
            const ticket = ticketFor(item);
            if (!ticket) return;
            const prices = rate(ticket), confirmed = quote?.items[index];
            const name = confirmed?.ticket_name ?? ticket.name;
            for (const [age,label] of [['adult','dewasa'],['child','anak']]) {
                if (!item[age]) continue;
                const price = cents(confirmed?.[`${age}_unit_price`] ?? prices[`${age}_price`]);
                const subtotal = price*item[age]; total+=subtotal;
                const row = el('div',undefined,'purchase-summary-row');
                row.append(el('span',`${name} · ${item[age]} ${label} × ${money.format(price/100)}`),el('span',money.format(subtotal/100)));
                summary.append(row);
            }
        });
        if (!summary.childElementCount) summary.append(el('p','Pilih tiket untuk melihat ringkasan pesanan.','purchase-muted'));
        byId('summary-date').textContent = visitDate.value ? `Tanggal kunjungan: ${visitDate.value.split('-').reverse().join('/')}` : '';
        byId('summary-total').textContent = money.format(quote ? cents(quote.total)/100 : total/100);
        byId('total-label').textContent = quote ? 'Total pesanan' : 'Total estimasi';
        add.disabled = busy || items.length>=4 || !available().some(ticket=>ticket.group===selectedGroup);
        next.disabled = busy || !available().length;
    }
    function labelInput(label,name,value,inputType='text') {
        const wrapper=el('label',label), input=el('input');
        input.name=name; input.type=inputType; input.value=value; input.required=true;
        input.id=`field-${dotName(name).replaceAll('.','-')}`;wrapper.htmlFor=input.id;input.setAttribute('aria-label',label);
        wrapper.append(input); errorTarget(wrapper,dotName(name));
        return {wrapper,input};
    }
    function renderGroups() {
        const choices = available();
        for (const [group,id] of [['masuk','entry-ticket-names'],['wahana','ride-ticket-names'],['lainnya','other-ticket-names']]) {
            const list=byId(id),button=list.closest('button');
            const groupTickets=choices.filter(ticket=>ticket.group===group);
            list.replaceChildren();
            groupTickets.forEach(ticket=>list.append(el('li',ticket.name)));
            if (!groupTickets.length) list.append(el('li','Belum ada tiket tersedia.'));
            button.hidden = group==='lainnya' && !groupTickets.length;
            button.setAttribute('aria-pressed',String(selectedGroup===group));
        }
    }
    function renderItems() {
        renderGroups(); itemList.replaceChildren();
        byId('catalog-empty').hidden = available().some(ticket=>ticket.group===selectedGroup);
        items.forEach((item,index)=>{
            const current=ticketFor(item);
            const choices=available().filter(ticket=>ticket.group===(current?.group??selectedGroup));
            const block=el('div',undefined,'purchase-item'),heading=el('div',undefined,'purchase-item-heading');
            heading.append(el('h3',`Tiket ${index+1}`));
            if (items.length>1) {
                const remove=el('button',`Hapus tiket ${index+1}`,'purchase-link'); remove.type='button';
                remove.addEventListener('click',()=>{items.splice(index,1);changed();renderItems();}); heading.append(remove);
            }
            const grid=el('div',undefined,'purchase-grid'),wrapper=el('label',`Pilihan tiket ${index+1}`),select=el('select');
            select.name=`items[${index}][ticket_reference]`;select.required=true;
            const placeholder=el('option','Pilih tiket');placeholder.value='';select.append(placeholder);
            choices.forEach(ticket=>{
                const option=el('option',`${ticket.name} — ${ticket.category}${ticket.available_quota<=0?' (Kuota habis)':''}`);
                option.value=ticket.reference;option.disabled=ticket.available_quota<=0;select.append(option);
            });
            if (!current) item.reference='';
            select.value=item.reference;wrapper.append(select);errorTarget(wrapper,`items.${index}.ticket_reference`);grid.append(wrapper);
            const details=el('p',undefined,'purchase-item-details');
            function describeTicket() {
                const ticket=ticketFor(item);
                if (!ticket) {details.textContent='Pilih tiket untuk melihat tarif, ketentuan, dan wahana.';return;}
                const prices=rate(ticket);
                details.textContent=`Dewasa ${money.format(cents(prices.adult_price)/100)} · Anak ${money.format(cents(prices.child_price)/100)} · Kuota tersedia: ${ticket.available_quota}`
                    +(ticket.wahana.length?` · Wahana: ${ticket.wahana.map(wahana=>wahana.name).join(', ')}`:'')
                    +(ticket.terms?` · Ketentuan: ${ticket.terms}`:'');
            }
            select.addEventListener('change',()=>{item.reference=select.value;changed();describeTicket();});
            for (const [age,label] of [['adult','Dewasa'],['child','Anak']]) {
                const {wrapper,input}=labelInput(`${label} (tiket ${index+1})`,`items[${index}][${age}_quantity]`,item[age],'number');
                input.min='0';input.max='4';input.step='1';
                const controls=el('div',undefined,'purchase-counter');
                for (const [symbol,delta,verb] of [['−',-1,'Kurangi'],['+',1,'Tambah']]) {
                    const button=el('button',symbol);button.type='button';button.setAttribute('aria-label',`${verb} ${label.toLowerCase()} tiket ${index+1}`);
                    button.addEventListener('click',()=>{input.value=quantity(item[age]+delta);item[age]=Number(input.value);changed();});
                    controls.append(button);
                }
                controls.insertBefore(input,controls.lastChild);wrapper.insertBefore(controls,wrapper.querySelector('[data-error]'));
                input.addEventListener('input',()=>{item[age]=quantity(input.value);changed();});grid.append(wrapper);
            }
            errorTarget(block,`items.${index}`);block.append(heading,grid,details);itemList.append(block);describeTicket();
        });updateSummary();
    }
    function bookerValue(key) { return form.querySelector(`[name="booker[${key}]"]`).value; }
    function syncBooker() {
        visitorList.querySelectorAll('[data-booker-key]').forEach(input=>input.value=bookerValue(input.dataset.bookerKey));
    }
    function renderVisitors() {
        visitorList.replaceChildren();
        const hasAdult=items.some(item=>item.adult>0);
        bookerVisits.disabled=!hasAdult;
        if (!hasAdult) bookerVisits.checked=false;
        byId('booker-heading').textContent=bookerVisits.checked?'Pengunjung 1 (Pemesan)':'Identitas Pemesan';
        byId('booker-help').textContent=hasAdult?'Pemesan yang ikut berkunjung dihitung sebagai satu pengunjung dewasa.':'Pesanan hanya berisi tiket anak. Pemesan dicatat terpisah sebagai pemesan.';
        let firstAdult=true,number=bookerVisits.checked?1:0;
        items.forEach((item,itemIndex)=>{
            let visitorIndex=0;
            errorTarget(visitorList,`items.${itemIndex}.visitors`);
            for (const [age,label] of [['adult','Dewasa'],['child','Anak']]) for (let n=0;n<item[age];n++) {
                const visitorKey=`${age}:${n}`, visitor=item.visitors[visitorKey]??={name:'',identity_number:''};
                const isBooker=bookerVisits.checked&&age==='adult'&&firstAdult;
                if(age==='adult') firstAdult=false;
                const card=el('div',undefined,'krm-card purchase-fields');
                if (isBooker) card.hidden=true;
                else card.append(el('h2',`Pengunjung ${++number} · ${label} · ${ticketFor(item)?.name??'Tiket'}`));
                const grid=el('div',undefined,'purchase-grid'),prefix=`items[${itemIndex}][visitors][${visitorIndex}]`;
                for (const [key,text,length] of [['name','Nama lengkap',255],['identity_number','Nomor identitas (KTP / KK / Paspor)',50]]) {
                    const {wrapper,input}=labelInput(text,`${prefix}[${key}]`,isBooker?bookerValue(key):visitor[key]);
                    input.maxLength=length;input.setAttribute('aria-label',`${text} — tiket ${itemIndex+1}, ${label.toLowerCase()} ${n+1}`);
                    if (isBooker) {input.dataset.bookerKey=key;input.readOnly=true;}
                    else input.addEventListener('input',()=>visitor[key]=input.value);
                    grid.append(wrapper);
                }
                const hidden=el('input');hidden.type='hidden';hidden.name=`${prefix}[age_category]`;hidden.value=age;
                card.append(grid,hidden);errorTarget(card,`items.${itemIndex}.visitors.${visitorIndex}`);visitorList.append(card);visitorIndex++;
            }
        });
    }
    function renderReview() {
        const target=byId('review-details');target.replaceChildren();
        const booker=el('div',undefined,'purchase-review-line');
        booker.append(el('h2','Identitas Pemesan'),el('p',bookerValue('name')),el('p',`Identitas: ${bookerValue('identity_number')}`),el('p',`${bookerValue('email')} · ${bookerValue('phone')}`));target.append(booker);
        items.forEach((item,index)=>{
            const block=el('div',undefined,'purchase-review-line');block.append(el('h2',ticketFor(item).name));
            const inputs=visitorList.querySelectorAll(`[name^="items[${index}][visitors]"]`);
            const visitors={};inputs.forEach(input=>{
                const parts=dotName(input.name).split('.');(visitors[parts[3]]??={})[parts[4]]=input.value;
            });
            Object.values(visitors).forEach(visitor=>block.append(el('p',`${visitor.name} · ${visitor.age_category==='adult'?'Dewasa':'Anak'} · ${visitor.identity_number}`)));
            target.append(block);
        });
    }
    function setStep(value,focus=true) {
        step=value;
        for (let n=1;n<=3;n++) sections[n].hidden=n!==step;
        next.hidden=step===3;previous.hidden=step===1;submit.hidden=step!==3;
        byId('submit-note').hidden=step!==3;byId('cancel-purchase').hidden=step!==1;
        next.textContent=step===2?'Tinjau Pesanan →':'Lanjut →';
        form.querySelectorAll('input[name^="booker["],#booker-is-visitor').forEach(input=>input.readOnly=step===3);
        document.querySelectorAll('[data-step]').forEach(node=>{
            const number=Number(node.dataset.step);node.classList.toggle('completed',number<step);
            if(number===step)node.setAttribute('aria-current','step');else node.removeAttribute('aria-current');
        });
        if(focus) byId(['','ticket-heading','visitor-heading','review-heading'][step]).focus();
    }
    async function advance() {
        if(busy||submitting)return;
        clearErrors();
        if(step===1) {
            if(!validateTickets()||!await refreshQuote())return;
            renderVisitors();setStep(2);
        } else if(step===2) {
            syncBooker();
            if(!validateSection(sections[2]))return;
            if(!await refreshQuote())return;
            renderReview();setStep(3);
        }
    }
    next.addEventListener('click',advance);
    previous.addEventListener('click',()=>{clearErrors();setStep(step-1);});
    visitDate.addEventListener('input',changed);
    type.addEventListener('change',()=>{changed();renderItems();});
    document.querySelectorAll('[data-group]').forEach(button=>button.addEventListener('click',()=>{
        if(selectedGroup===button.dataset.group)return;
        selectedGroup=button.dataset.group;
        items.filter(item=>!item.reference).forEach(item=>item.reference='');
        if(items.length===1) {items[0].reference='';}
        changed();renderItems();
    }));
    add.addEventListener('click',()=>{if(items.length<4){items.push(normalize());changed();renderItems();}});
    bookerVisits.addEventListener('change',renderVisitors);
    form.querySelectorAll('[name^="booker["]').forEach(input=>input.addEventListener('input',syncBooker));
    form.addEventListener('submit',event=>{
        if(submitting){event.preventDefault();return;}
        if(step!==3){event.preventDefault();advance();return;}
        clearErrors();
        if(!validateTickets()){event.preventDefault();setStep(1,false);return;}
        syncBooker();
        if(!validateSection(sections[2])){event.preventDefault();setStep(2,false);return;}
        submitting=true;submit.disabled=true;submit.textContent='Menyimpan…';form.setAttribute('aria-busy','true');
    });
    window.addEventListener('pageshow',()=>{submitting=false;submit.disabled=false;submit.textContent='Simpan Pesanan';form.removeAttribute('aria-busy');});
    const initial=ticketFor(items[0]);if(initial) selectedGroup=initial.group;
    renderItems();
    const keys=Object.keys(serverErrors);
    if(keys.length) {
        if(keys.every(key=>key.startsWith('booker')||key.includes('visitors'))){renderVisitors();setStep(2,false);}
        showErrors(serverErrors);
    }
})();
</script>
