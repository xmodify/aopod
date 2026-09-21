// AOPOD Agent Client Application JS

let syncStartPicker, syncEndPicker;

document.addEventListener('DOMContentLoaded', () => {
  initTabs();
  initDatePickers();
  initPresets();
  loadStatus();
  loadConfig();
  startLogPolling();

  // Bind Buttons
  document.getElementById('btnSyncNow')?.addEventListener('click', handleSyncNow);
  document.getElementById('btnTestDb')?.addEventListener('click', handleTestDb);
  document.getElementById('btnTestApi')?.addEventListener('click', handleTestApi);
  document.getElementById('btnSaveConfig')?.addEventListener('click', handleSaveConfig);
  document.getElementById('btnOpenConfigFolder')?.addEventListener('click', handleOpenConfigFolder);
  document.getElementById('btnClearLogs')?.addEventListener('click', () => {
    document.getElementById('logTerminal').innerHTML = '';
  });
});

function initDatePickers() {
  const modalEnd = new Date();
  const modalStart = new Date();
  modalStart.setDate(modalStart.getDate() - 10);

  if (typeof initThaiDatePicker === 'function') {
    syncStartPicker = initThaiDatePicker('#startDate', {
      defaultDate: modalStart
    });
    syncEndPicker = initThaiDatePicker('#endDate', {
      defaultDate: modalEnd
    });
  }
}

async function handleOpenConfigFolder() {
  try {
    await fetch('/api/open-config-folder');
  } catch (e) {
    alert('ไม่สามารถเปิดโฟลเดอร์ได้: ' + e.message);
  }
}

// Tab Navigation
function initTabs() {
  const tabBtns = document.querySelectorAll('.tab-btn');
  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      tabBtns.forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

      btn.classList.add('active');
      const targetId = btn.getAttribute('data-tab');
      document.getElementById(targetId)?.classList.add('active');
    });
  });
}

// Date Presets
function initPresets() {
  const startInput = document.getElementById('startDate');
  const endInput = document.getElementById('endDate');

  document.querySelectorAll('.preset-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const days = parseInt(btn.getAttribute('data-days'), 10);
      const end = new Date();
      let start = new Date();

      if (days === 0) { // Today
        start = new Date();
      } else if (days === -1) { // Current Month
        start = new Date(end.getFullYear(), end.getMonth(), 1);
      } else {
        start.setDate(start.getDate() - days);
      }

      if (syncStartPicker) syncStartPicker.setDate(start, true);
      else if (startInput) startInput.value = start.toISOString().split('T')[0];

      if (syncEndPicker) syncEndPicker.setDate(end, true);
      else if (endInput) endInput.value = end.toISOString().split('T')[0];
    });
  });
}

function formatThaiDateTime(dateStr) {
  if (!dateStr || dateStr === '-' || dateStr === 'ยังไม่มีประวัติการส่ง') return 'ยังไม่มีประวัติการส่ง';
  const d = new Date(dateStr.replace(' ', 'T'));
  if (isNaN(d.getTime())) return dateStr;
  const thMonths = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
  const day = d.getDate();
  const month = thMonths[d.getMonth()];
  const year = d.getFullYear() + 543;
  const time = `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}:${String(d.getSeconds()).padStart(2, '0')}`;
  return `${day} ${month} ${year} ${time}`;
}

// Load System Status
async function loadStatus() {
  try {
    const res = await fetch('/api/status');
    const data = await res.json();

    document.getElementById('hospTitle').textContent = `${data.hospital_name || 'โรงพยาบาล'} (${data.hospital_code || '-'})`;
    document.getElementById('dbStatusText').textContent = data.db_status === 'connected' ? '🟢 เชื่อมต่อสำเร็จ' : '🔴 ไม่สามารถเชื่อมต่อได้';
    document.getElementById('serverStatusText').textContent = data.server_status === 'connected' ? '🟢 ออนไลน์' : '⚪ ยังไม่เชื่อมต่อ';
    document.getElementById('serviceStatusText').textContent = data.service_running ? '🟢 กำลังทำงาน (Running)' : '⚪ หยุดทำงาน (Stopped)';
    document.getElementById('lastSyncText').textContent = formatThaiDateTime(data.last_sync);
    if (data.config_path && document.getElementById('cfgLocationPath')) {
      document.getElementById('cfgLocationPath').textContent = data.config_path;
    }

    // Update badges
    const statusBadge = document.getElementById('statusBadge');
    if (data.db_status === 'connected') {
      statusBadge.className = 'badge badge-online';
      statusBadge.innerHTML = '<i class="fa-solid fa-circle text-success" style="font-size: 0.55rem;"></i> พร้อมทำงาน';
    } else {
      statusBadge.className = 'badge badge-offline';
      statusBadge.innerHTML = '<i class="fa-solid fa-circle text-danger" style="font-size: 0.55rem;"></i> ขัดข้อง';
    }

    const versionBadge = document.querySelector('.badge-version');
    if (versionBadge && data.version) {
      versionBadge.textContent = `v${data.version}`;
    }
  } catch (e) {
    console.error('Error loading status:', e);
  }
}

function onScheduleTypeChange() {
  const type = document.getElementById('cfgScheduleType').value;
  const panelDaily = document.getElementById('panelDailyTime');
  const panelHourly = document.getElementById('panelHourlyTime');
  const panelMinute = document.getElementById('panelMinuteTime');

  if (panelDaily) panelDaily.style.display = type === 'daily' ? 'flex' : 'none';
  if (panelHourly) panelHourly.style.display = type === 'hourly' ? 'flex' : 'none';
  if (panelMinute) panelMinute.style.display = type === 'minute' ? 'flex' : 'none';
}

// Load Config into Form
async function loadConfig() {
  try {
    const res = await fetch('/api/config');
    const cfg = await res.json();

    // Hospital
    document.getElementById('cfgHospCode').value = cfg.hospital?.code || '';
    document.getElementById('cfgHospName').value = cfg.hospital?.name || '';
    document.getElementById('cfgToken').value = cfg.hospital?.token || '';
    document.getElementById('cfgServerUrl').value = cfg.hospital?.server_url || '';
    document.getElementById('cfgBedQty').value = cfg.hospital?.bed_qty || 30;

    // Database
    document.getElementById('cfgDbHost').value = cfg.database?.host || '127.0.0.1';
    document.getElementById('cfgDbPort').value = cfg.database?.port || 3306;
    document.getElementById('cfgDbUser').value = cfg.database?.username || 'rims';
    document.getElementById('cfgDbPass').value = cfg.database?.password || '';
    document.getElementById('cfgDbName').value = cfg.database?.database || 'hosxp';

    // Schedule (3 unified options: Daily, Hourly, Minute)
    const sched = cfg.schedule || {};
    document.getElementById('cfgScheduleType').value = sched.type || 'daily';
    document.getElementById('cfgDailyHour').value = String(sched.daily_hour ?? 2).padStart(2, '0');
    document.getElementById('cfgDailyMinute').value = String(sched.daily_minute ?? 0).padStart(2, '0');
    document.getElementById('cfgIntervalHours').value = sched.interval_hours || 1;
    document.getElementById('cfgIntervalMins').value = sched.interval_mins || 15;
    document.getElementById('cfgSyncDays').value = sched.sync_days_back || 30;
    
    onScheduleTypeChange();
  } catch (e) {
    console.error('Error loading config:', e);
  }
}

// Save Config
async function handleSaveConfig(e) {
  e.preventDefault();
  const btn = document.getElementById('btnSaveConfig');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> กำลังบันทึก...';

  const payload = {
    hospital: {
      code: document.getElementById('cfgHospCode').value,
      name: document.getElementById('cfgHospName').value,
      token: document.getElementById('cfgToken').value,
      server_url: document.getElementById('cfgServerUrl').value,
      bed_qty: parseInt(document.getElementById('cfgBedQty').value, 10) || 30,
    },
    database: {
      driver: 'mysql',
      host: document.getElementById('cfgDbHost').value,
      port: parseInt(document.getElementById('cfgDbPort').value, 10) || 3306,
      username: document.getElementById('cfgDbUser').value,
      password: document.getElementById('cfgDbPass').value,
      database: document.getElementById('cfgDbName').value,
    },
    schedule: {
      type: document.getElementById('cfgScheduleType').value,
      daily_hour: parseInt(document.getElementById('cfgDailyHour').value, 10) || 0,
      daily_minute: parseInt(document.getElementById('cfgDailyMinute').value, 10) || 0,
      interval_hours: parseInt(document.getElementById('cfgIntervalHours').value, 10) || 1,
      interval_mins: parseInt(document.getElementById('cfgIntervalMins').value, 10) || 15,
      sync_days_back: parseInt(document.getElementById('cfgSyncDays').value, 10) || 30,
      chunk_size: 0, // Auto-tuned by Go Agent
      threads: 0,    // Auto-tuned by Go Agent
    }
  };

  try {
    const res = await fetch('/api/config', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const result = await res.json();
    alert(result.message || 'บันทึกการตั้งค่าเรียบร้อยแล้ว');
    loadStatus();
  } catch (err) {
    alert('เกิดข้อผิดพลาดในการบันทึก: ' + err.message);
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> บันทึกการตั้งค่า';
  }
}

// Test DB Connection
async function handleTestDb() {
  const btn = document.getElementById('btnTestDb');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> กำลังทดสอบ...';

  const payload = {
    host: document.getElementById('cfgDbHost').value.trim(),
    port: parseInt(document.getElementById('cfgDbPort').value.trim(), 10) || 3306,
    username: document.getElementById('cfgDbUser').value.trim(),
    password: document.getElementById('cfgDbPass').value,
    database: document.getElementById('cfgDbName').value.trim(),
  };

  try {
    const res = await fetch('/api/test-db', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const result = await res.json();
    if (result.status === 'connected') {
      alert(`✅ เชื่อมต่อ HOSxP สำเร็จ!\n- โรงพยาบาล: ${result.hospital_name} (${result.hospital_code})\n- Latency: ${result.latency_ms} ms\n- จำนวนตาราง: ${result.table_count} ตาราง`);
      if (result.hospital_code && !document.getElementById('cfgHospCode').value) {
        document.getElementById('cfgHospCode').value = result.hospital_code;
      }
      if (result.hospital_name && !document.getElementById('cfgHospName').value) {
        document.getElementById('cfgHospName').value = result.hospital_name;
      }
    } else {
      alert(`❌ เชื่อมต่อไม่สำเร็จ: ${result.error || result.message}`);
    }
    loadStatus();
  } catch (err) {
    alert('เกิดข้อผิดพลาด: ' + err.message);
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-plug"></i> ทดสอบเชื่อมต่อ HOSxP';
  }
}

// Test API Connection
async function handleTestApi() {
  const btn = document.getElementById('btnTestApi');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> กำลังตรวจสอบ...';

  const payload = {
    server_url: document.getElementById('cfgServerUrl').value.trim(),
    token: document.getElementById('cfgToken').value.trim(),
    hospcode: document.getElementById('cfgHospCode').value.trim(),
  };

  try {
    const res = await fetch('/api/test-api', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const result = await res.json();
    if (result.status === 'success') {
      alert(`✅ ${result.message} (HTTP ${result.http_code}, ${result.latency_ms} ms)`);
    } else {
      alert(`⚠️ ${result.message}`);
    }
    loadStatus();
  } catch (err) {
    alert('เกิดข้อผิดพลาด: ' + err.message);
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> ทดสอบ Token / Server';
  }
}

// Manual Sync Now
async function handleSyncNow() {
  const startDate = document.getElementById('startDate').value;
  const endDate = document.getElementById('endDate').value;
  const btn = document.getElementById('btnSyncNow');
  const progressContainer = document.getElementById('progressBarContainer');
  const progressFill = document.getElementById('progressBarFill');

  if (!startDate || !endDate) {
    alert('กรุณาระบุช่วงวันที่ให้ครบถ้วน');
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> กำลังประมวลผลและส่งข้อมูล...';
  progressContainer.style.display = 'block';
  progressFill.style.width = '30%';

  try {
    const res = await fetch('/api/sync', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ start_date: startDate, end_date: endDate }),
    });
    progressFill.style.width = '80%';
    const result = await res.json();
    progressFill.style.width = '100%';

    if (result.success) {
      alert(`✅ ${result.message}\n- OPD: ส่งสำเร็จ ${result.opd?.total_sent || 0} รายการ\n- IPD: ส่งสำเร็จ ${result.ipd?.total_sent || 0} รายการ\n- เตียง: ส่งสำเร็จ`);
    } else {
      alert(`⚠️ การส่งข้อมูล: ${result.message}`);
    }
    loadStatus();
  } catch (err) {
    alert('เกิดข้อผิดพลาดในการส่งข้อมูล: ' + err.message);
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> เริ่มส่งข้อมูล (Sync Now)';
    setTimeout(() => {
      progressContainer.style.display = 'none';
      progressFill.style.width = '0%';
    }, 2000);
  }
}

// Windows Service Actions
async function handleServiceAction(action) {
  if (!confirm(`คุณต้องการ ${action} Windows Service ใช่หรือไม่?`)) return;

  try {
    const res = await fetch(`/api/service/${action}`, { method: 'POST' });
    const result = await res.json();
    alert(result.message || 'ดำเนินการเรียบร้อยแล้ว');
    loadStatus();
  } catch (err) {
    alert('เกิดข้อผิดพลาด: ' + err.message);
  }
}

// Live Log Polling
function startLogPolling() {
  const terminal = document.getElementById('logTerminal');

  setInterval(async () => {
    try {
      const res = await fetch('/api/logs');
      const logs = await res.json();

      if (terminal && Array.isArray(logs)) {
        terminal.innerHTML = logs.map(l => 
          `<div class="log-line ${l.level}">[${l.timestamp}] [${l.level}] ${escapeHtml(l.message)}</div>`
        ).join('');
        terminal.scrollTop = terminal.scrollHeight;
      }
    } catch (e) {
      // ignore log poll errors
    }
  }, 2000);
}

function escapeHtml(str) {
  if (!str) return '';
  return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
