<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Request a Free Quote — Impact Glass</title>
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; background: #f4f5f7; color: #111; font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    .wrap { max-width: 640px; margin: 40px auto; padding: 0 20px; }
    .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
    .head { background: #0f766e; color: #fff; padding: 24px 28px; }
    .head h1 { margin: 0; font-size: 22px; }
    .head p { margin: 8px 0 0; opacity: .9; font-size: 14px; }
    form { padding: 24px 28px; }
    .row { display: flex; gap: 16px; }
    .field { margin-bottom: 16px; flex: 1; }
    label.lbl { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    input, select, textarea { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-family: inherit; }
    .hint { margin: -8px 0 16px; font-size: 12px; color: #6b7280; }
    .appt { border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px 16px 4px; margin-bottom: 16px; background: #f9fafb; }
    button { width: 100%; padding: 12px 16px; background: #0f766e; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; }
    button:disabled { background: #5e9e97; cursor: default; }
    .result { margin: 0 28px 28px; border-radius: 10px; padding: 14px 16px; }
    .result.ok { border: 1px solid #a7f3d0; background: #ecfdf5; }
    .result.warn { border: 1px solid #fde68a; background: #fffbeb; }
    .rrow { display: flex; gap: 8px; font-size: 14px; margin-top: 4px; }
    .muted { font-size: 12px; color: #6b7280; margin-top: 10px; }
    .cid { font-size: 13px; margin-top: 8px; color: #374151; }
    a.crm { color: #0f766e; font-weight: 600; font-size: 14px; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="head">
        <h1>Request a Free Quote</h1>
        <p>AA Glass &amp; Windows — tell us about your project</p>
      </div>

      <form id="quoteForm">
        <div class="field" style="flex:none">
          <label class="lbl">Full name *</label>
          <input name="fullName" required>
        </div>

        <div class="row">
          <div class="field"><label class="lbl">Phone</label><input type="tel" name="phone"></div>
          <div class="field"><label class="lbl">Email</label><input type="email" name="email"></div>
        </div>
        <p class="hint">Provide at least an email or a phone number.</p>

        <div class="field" style="flex:none">
          <label class="lbl">Property address *</label>
          <input name="propertyAddress" placeholder="Street, city, state, ZIP" required>
        </div>

        <div class="row">
          <div class="field">
            <label class="lbl">Is the property a house or a condo?</label>
            <select name="propertyType">
              <option value="" selected>Select…</option>
              <option value="House">House</option>
              <option value="Condo">Condo</option>
            </select>
          </div>
          <div class="field">
            <label class="lbl">Are you one of the homeowners?</label>
            <select name="homeowner">
              <option value="" selected>Select…</option>
              <option value="Yes">Yes</option>
              <option value="No">No</option>
            </select>
          </div>
        </div>

        <div class="field" style="flex:none">
          <label class="lbl">Approximately how many windows are you looking to replace?</label>
          <input type="number" min="0" name="windowCount">
        </div>

        <div class="field" style="flex:none">
          <label class="lbl">Do you also need to replace any entry doors or sliding glass doors? If so, what type?</label>
          <input name="doors" placeholder="e.g. 1 sliding glass door, 1 front entry door">
        </div>

        <div class="field" style="flex:none">
          <label class="lbl">What is your main reason for replacing your windows and doors?</label>
          <select name="reason">
            <option value="" selected>Select…</option>
            <option value="Hurricane protection">Hurricane protection</option>
            <option value="Energy efficiency">Energy efficiency</option>
            <option value="Remodeling">Remodeling</option>
            <option value="Insurance savings">Insurance savings</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="field" style="flex:none">
          <label class="lbl">Are you looking to replace all of your windows and doors, or only certain ones?</label>
          <select name="scope">
            <option value="" selected>Select…</option>
            <option value="All windows and doors">All windows and doors</option>
            <option value="Only certain ones">Only certain ones</option>
          </select>
        </div>

        <div class="field" style="flex:none">
          <label class="lbl">When are you hoping to complete the project?</label>
          <select name="timeline">
            <option value="" selected>Select…</option>
            <option value="ASAP">ASAP</option>
            <option value="Within 1–3 months">Within 1–3 months</option>
            <option value="Just gathering estimates">Just gathering estimates</option>
          </select>
        </div>

        <div class="field" style="flex:none">
          <label class="lbl">Will all decision-makers be available during the appointment?</label>
          <select name="decisionMakers">
            <option value="" selected>Select…</option>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
          </select>
        </div>

        <div class="field" style="flex:none">
          <label class="lbl">How did you hear about AA Glass &amp; Windows?</label>
          <input name="referralSource" placeholder="Google, referral, social media, etc.">
        </div>

        <div class="appt">
          <label class="lbl" style="font-size:14px;margin-bottom:4px">Preferred appointment (optional)</label>
          <p class="hint" style="margin:0 0 14px">
            Live availability pulled from our team's calendars. Pick a time and we'll do our best to confirm it.
          </p>
          <div id="apptStatus" class="hint" style="margin:0">Loading available times…</div>
          <div id="apptControls" style="display:none">
            <div class="field" style="flex:none">
              <label class="lbl">Who would you like to meet with?</label>
              <select id="apptCalendar" name="appointmentCalendarId">
                <option value="">Select a team member…</option>
              </select>
            </div>
            <div class="row" id="apptDayTimeRow" style="display:none">
              <div class="field">
                <label class="lbl">Day</label>
                <select id="apptDay"><option value="">Select a day…</option></select>
              </div>
              <div class="field">
                <label class="lbl">Time</label>
                <select id="apptTime" name="appointmentSlot" disabled><option value="">Pick a day first</option></select>
              </div>
            </div>
          </div>
        </div>

        <p class="hint" style="margin-top:4px">
          Submitting creates a lead in GoHighLevel. Your GoHighLevel Workflow then
          emails the team at {{ $notificationEmail }}.
        </p>

        <button type="submit" id="submitBtn">Request Quote</button>
      </form>

      <div id="resultBox"></div>
    </div>
  </div>

  <script>
    const AVAILABILITY_URL = "{{ route('ghl.availability') }}";
    const QUOTE_URL = "{{ route('ghl.quote.store') }}";
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    const form = document.getElementById('quoteForm');
    const btn = document.getElementById('submitBtn');
    const box = document.getElementById('resultBox');

    function esc(s) { return String(s).replace(/[&<>"]/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[c])); }
    function rrow(ok, text) { return '<div class="rrow"><span>' + (ok ? '✅' : '⚠️') + '</span><span>' + esc(text) + '</span></div>'; }

    function render(r) {
      let html = '';
      if (r.ok) {
        html += rrow(true, r.isNewContact ? 'Thanks! A new lead was created in GoHighLevel.' : 'Thanks! An existing contact was matched and updated in GoHighLevel.');
        html += rrow(!!r.verifiedInGhl, r.verifiedInGhl ? 'Confirmed in GoHighLevel via API read-back.' : "Created, but couldn't be re-read from the API to confirm.");
        html += rrow(!!r.notificationTriggered, r.notificationTriggered ? 'Notification workflow re-triggered (“Website Quote Form” tag applied).' : "Tag couldn't be applied — the notification workflow may not fire.");
        if (r.appointmentRequested) {
          html += rrow(!!r.appointmentBooked, r.appointmentBooked
            ? ('Appointment booked' + (r.appointmentTime ? ' for ' + new Date(r.appointmentTime).toLocaleString() : '') + '.')
            : ("Couldn't book the requested time" + (r.appointmentError ? ' (' + r.appointmentError + ')' : '') + ' — the team will reach out to schedule.'));
        }
        if (r.contactName) html += rrow(true, 'Contact: ' + r.contactName);
        if (r.tags && r.tags.length) html += rrow(true, 'Tags: ' + r.tags.join(', '));
        if (r.dateAdded) html += rrow(true, 'Added: ' + new Date(r.dateAdded).toLocaleString());
        if (r.noteAdded === false) html += rrow(false, "(Details note couldn't be attached, but the lead was created.)");
        if (r.contactId) html += '<div class="cid">Contact id: <code>' + esc(r.contactId) + '</code></div>';
        if (r.crmUrl) html += '<div style="margin-top:8px"><a class="crm" target="_blank" rel="noopener noreferrer" href="' + esc(r.crmUrl) + '">View in GoHighLevel →</a></div>';
        box.className = 'result ok';
      } else {
        html += rrow(false, 'Something went wrong: ' + (r.error || 'please try again.'));
        box.className = 'result warn';
      }
      box.innerHTML = html;
    }

    // --- Appointment availability ---
    const apptStatus = document.getElementById('apptStatus');
    const apptControls = document.getElementById('apptControls');
    const calSel = document.getElementById('apptCalendar');
    const dayTimeRow = document.getElementById('apptDayTimeRow');
    const daySel = document.getElementById('apptDay');
    const timeSel = document.getElementById('apptTime');
    let availability = null;

    function dayLabel(date) { const d = new Date(date + 'T00:00:00'); return isNaN(d) ? date : d.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' }); }
    function timeLabel(iso) { const d = new Date(iso); return isNaN(d) ? iso : d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }); }
    function selectedCalendar() { return availability ? availability.calendars.find(c => c.id === calSel.value) || null : null; }
    function resetTime(msg) { timeSel.innerHTML = '<option value="">' + msg + '</option>'; timeSel.disabled = true; }

    function onCalendarChange() {
      const cal = selectedCalendar();
      daySel.innerHTML = '<option value="">Select a day…</option>';
      resetTime('Pick a day first');
      if (!cal) { dayTimeRow.style.display = 'none'; return; }
      cal.days.forEach(d => { const o = document.createElement('option'); o.value = d.date; o.textContent = dayLabel(d.date) + ' (' + d.slots.length + ' open)'; daySel.appendChild(o); });
      dayTimeRow.style.display = cal.days.length ? 'flex' : 'none';
    }
    function onDayChange() {
      const cal = selectedCalendar();
      const day = cal ? cal.days.find(d => d.date === daySel.value) : null;
      if (!day) { resetTime('Pick a day first'); return; }
      timeSel.innerHTML = '<option value="">Select a time…</option>';
      day.slots.forEach(s => { const o = document.createElement('option'); o.value = s; o.textContent = timeLabel(s); timeSel.appendChild(o); });
      timeSel.disabled = false;
    }
    calSel.addEventListener('change', onCalendarChange);
    daySel.addEventListener('change', onDayChange);

    fetch(AVAILABILITY_URL)
      .then(r => r.json())
      .then(data => {
        availability = data;
        if (!data.calendars || !data.calendars.length) { apptStatus.textContent = 'No calendars are available to book right now.'; return; }
        data.calendars.forEach(c => { const o = document.createElement('option'); o.value = c.id; o.textContent = c.name + (c.days.length ? '' : ' (no openings)'); if (!c.days.length) o.disabled = true; calSel.appendChild(o); });
        apptStatus.style.display = 'none';
        apptControls.style.display = 'block';
      })
      .catch(() => { apptStatus.textContent = "Couldn't load availability right now — you can still submit and we'll reach out to schedule."; });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      btn.disabled = true; btn.textContent = 'Submitting…'; box.innerHTML = ''; box.className = '';
      const fd = new FormData(form);
      const payload = Object.fromEntries(fd.entries());
      if (payload.appointmentSlot) {
        const cal = selectedCalendar();
        if (cal) payload.appointmentCalendarName = cal.name;
      } else {
        delete payload.appointmentCalendarId;
        delete payload.appointmentSlot;
      }
      try {
        const res = await fetch(QUOTE_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          body: JSON.stringify(payload),
        });
        const data = await res.json();
        render(data);
        if (data.ok) { form.reset(); onCalendarChange(); }
      } catch (err) {
        render({ ok: false, error: String(err) });
      } finally {
        btn.disabled = false; btn.textContent = 'Request Quote';
      }
    });
  </script>
</body>
</html>
