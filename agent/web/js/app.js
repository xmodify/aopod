// AOPOD Agent Client Application JS

let syncStartPicker, syncEndPicker;
let currentHospCode = '10989';

document.addEventListener('DOMContentLoaded', () => {
  initTabs();
  initDatePickers();
  initPresets();
  initSettingsModal();
  loadStatus();
  loadConfig();
  startLogPolling();

  // Bind Buttons
  document.getElementById('btnSyncNow')?.addEventListener('click', handleSyncNow);
  document.getElementById('btnTestDb')?.addEventListener('click', handleTestDb);
  document.getElementById('btnTestApi')?.addEventListener('click', handleTestApi);
  document.getElementById('btnSaveConfig')?.addEventListener('click', handleSaveConfig);
  document.getElementById('btnOpenConfigFolder')?.addEventListener('click', handleOpenConfigFolder);
  document.getElementById('chkAutoStart')?.addEventListener('change', async (e) => {
    try {
      await fetch('/api/autostart', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ enabled: e.target.checked })
      });
      loadStatus();
    } catch (err) {
      alert('ไม่สามารถเปลี่ยนการตั้งค่า Auto-Start ได้: ' + err.message);
    }
  });
  document.getElementById('btnClearLogs')?.addEventListener('click', async () => {
    if (!confirm('คุณต้องการล้างและลบประวัติ Log ทั้งหมดใช่หรือไม่?')) return;
    try {
      const res = await fetch('/api/logs/clear', { method: 'POST' });
      const data = await res.json();
      const terminal = document.getElementById('logTerminal');
      if (terminal) {
        terminal.innerHTML = `<div class="log-line INFO">[System] ${data.message || 'ล้างประวัติ Log เรียบร้อยแล้ว'}</div>`;
      }
    } catch (err) {
      console.error('Failed to clear logs:', err);
    }
  });

  document.getElementById('btnOpenLogsFolder')?.addEventListener('click', async () => {
    try {
      await fetch('/api/open-logs-folder');
    } catch (err) {
      console.error('Failed to open logs folder:', err);
    }
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

// Settings & Password Modal Logic
function initSettingsModal() {
  const btnOpenSettings = document.getElementById('btnOpenSettings');
  const passwordModal = document.getElementById('passwordModal');
  const settingsModal = document.getElementById('settingsModal');
  const btnClosePasswordModal = document.getElementById('btnClosePasswordModal');
  const btnCancelPassword = document.getElementById('btnCancelPassword');
  const btnSubmitPassword = document.getElementById('btnSubmitPassword');
  const btnTogglePasswordEye = document.getElementById('btnTogglePasswordEye');
  const inputSettingsPassword = document.getElementById('inputSettingsPassword');
  const passwordError = document.getElementById('passwordError');
  const btnCloseSettingsModal = document.getElementById('btnCloseSettingsModal');
  const btnDismissSettings = document.getElementById('btnDismissSettings');

  function openPasswordModal() {
    // ซ่อนหน้าต่างตั้งค่าเดิมหากเปิดอยู่ เพื่อความปลอดภัย
    if (settingsModal) settingsModal.style.display = 'none';

    if (passwordError) {
      passwordError.style.display = 'none';
      passwordError.innerText = '';
    }
    if (inputSettingsPassword) {
      inputSettingsPassword.value = '';
    }
    if (passwordModal) {
      passwordModal.style.display = 'flex';
      setTimeout(() => inputSettingsPassword?.focus(), 100);
    }
  }

  function closePasswordModal() {
    if (passwordModal) passwordModal.style.display = 'none';
    if (inputSettingsPassword) inputSettingsPassword.value = '';
  }

  function openSettingsModal() {
    if (settingsModal) {
      settingsModal.style.display = 'flex';
      loadConfig();
    }
  }

  function closeSettingsModal() {
    if (settingsModal) settingsModal.style.display = 'none';
  }

  // Open settings trigger - ถาม password ทุกครั้งที่กดปุ่มตั้งค่า (ไม่มีการจำ session)
  btnOpenSettings?.addEventListener('click', () => {
    openPasswordModal();
  });

  // Close triggers
  btnClosePasswordModal?.addEventListener('click', closePasswordModal);
  btnCancelPassword?.addEventListener('click', closePasswordModal);
  btnCloseSettingsModal?.addEventListener('click', closeSettingsModal);
  btnDismissSettings?.addEventListener('click', closeSettingsModal);

  // Close password modal on Escape key only (ไม่ปิด settingsModal ด้วย Escape หรือ backdrop click เพื่อป้องกันค่าที่กำลังตั้งค่าหลุด)
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closePasswordModal();
    }
  });

  // Toggle eye visibility
  btnTogglePasswordEye?.addEventListener('click', () => {
    const eyeIcon = document.getElementById('eyeIcon');
    if (!inputSettingsPassword || !eyeIcon) return;

    if (inputSettingsPassword.type === 'password') {
      inputSettingsPassword.type = 'text';
      eyeIcon.classList.remove('fa-eye');
      eyeIcon.classList.add('fa-eye-slash');
    } else {
      inputSettingsPassword.type = 'password';
      eyeIcon.classList.remove('fa-eye-slash');
      eyeIcon.classList.add('fa-eye');
    }
  });

  // Password verification (รองรับ Aopod2026 หรือ Aopod ไม่ต้องจำรหัส รพ. ในการติดตั้งใหม่)
  function verifyPassword() {
    const inputPass = inputSettingsPassword ? inputSettingsPassword.value.trim() : '';
    if (!inputPass) {
      if (passwordError) {
        passwordError.innerText = 'กรุณากรอกรหัสผ่าน';
        passwordError.style.display = 'block';
      }
      return;
    }

    let hcode = currentHospCode;
    if (!hcode) {
      const val = document.getElementById('cfgHospCode')?.value?.trim();
      if (val) hcode = val;
    }

    const baseStr = String.fromCharCode(65, 111, 112, 111, 100); // "Aopod"
    const allowed = [
      baseStr + '2026',                // Aopod2026
      baseStr.toLowerCase() + '2026',  // aopod2026
      baseStr,                          // Aopod
      baseStr.toLowerCase()             // aopod
    ];
    if (hcode) {
      allowed.push(baseStr + hcode);                // Aopod<hcode>
      allowed.push(baseStr.toLowerCase() + hcode);  // aopod<hcode>
    }

    const cleanInput = inputPass.toLowerCase();
    const isMatched = allowed.some(pass => pass.toLowerCase() === cleanInput);

    if (isMatched) {
      closePasswordModal();
      openSettingsModal();
    } else {
      if (passwordError) {
        passwordError.innerText = '❌ รหัสผ่านไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง';
        passwordError.style.display = 'block';
      }
      inputSettingsPassword?.select();
    }
  }

  btnSubmitPassword?.addEventListener('click', verifyPassword);
  inputSettingsPassword?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      verifyPassword();
    }
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

    if (data.hospital_code) {
      currentHospCode = data.hospital_code;
    }

    document.getElementById('dbStatusText').textContent = data.db_status === 'connected' ? '🟢 เชื่อมต่อสำเร็จ' : '🔴 ไม่สามารถเชื่อมต่อได้';
    document.getElementById('serverStatusText').textContent = data.server_status === 'connected' ? '🟢 ออนไลน์' : '⚪ ยังไม่เชื่อมต่อ';
    const autostartEl = document.getElementById('autostartStatusText');
    if (autostartEl) {
      autostartEl.textContent = data.autostart_enabled ? '🟢 เปิดอัตโนมัติ' : '⚪ ปิดอยู่';
    }
    const chkAutoStart = document.getElementById('chkAutoStart');
    if (chkAutoStart) {
      chkAutoStart.checked = !!data.autostart_enabled;
    }
    document.getElementById('lastSyncText').textContent = formatThaiDateTime(data.last_sync);
    if (data.config_path && document.getElementById('cfgLocationPath')) {
      document.getElementById('cfgLocationPath').textContent = data.config_path;
    }

    // Update badges
    const statusBadge = document.getElementById('statusBadge');
    if (statusBadge) {
      if (data.db_status === 'connected') {
        statusBadge.className = 'badge badge-online';
        statusBadge.innerHTML = '<i class="fa-solid fa-circle text-success" style="font-size: 0.5rem;"></i> พร้อมทำงาน';
      } else {
        statusBadge.className = 'badge badge-offline';
        statusBadge.innerHTML = '<i class="fa-solid fa-circle text-danger" style="font-size: 0.5rem;"></i> ขัดข้อง';
      }
    }

    const versionBadge = document.getElementById('versionBadge');
    if (versionBadge && data.version) {
      versionBadge.textContent = `v${data.version}`;
    }
  } catch (e) {
    console.error('Error loading status:', e);
  }
}

// Load Config into Form
async function loadConfig() {
  try {
    const res = await fetch('/api/config');
    const cfg = await res.json();

    if (cfg.hospital?.code) {
      currentHospCode = cfg.hospital.code;
    }

    // Hospital
    document.getElementById('cfgHospCode').value = cfg.hospital?.code || '';
    document.getElementById('cfgHospName').value = cfg.hospital?.name || '';
    document.getElementById('cfgToken').value = cfg.hospital?.token || '';
    document.getElementById('cfgServerUrl').value = cfg.hospital?.server_url || '';

    // Database
    document.getElementById('cfgDbHost').value = cfg.database?.host || '';
    document.getElementById('cfgDbPort').value = cfg.database?.port || 3306;
    document.getElementById('cfgDbName').value = cfg.database?.database || '';
    document.getElementById('cfgDbUser').value = cfg.database?.username || '';
    document.getElementById('cfgDbPass').value = cfg.database?.password || '';
  } catch (e) {
    console.error('Error loading config:', e);
  }
}

// Save Config Form
async function handleSaveConfig() {
  const btn = document.getElementById('btnSaveConfig');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> กำลังบันทึก...';

  const payload = {
    hospital: {
      code: document.getElementById('cfgHospCode').value.trim(),
      name: document.getElementById('cfgHospName').value.trim(),
      token: document.getElementById('cfgToken').value.trim(),
      server_url: document.getElementById('cfgServerUrl').value.trim(),
    },
    database: {
      driver: 'mysql',
      host: document.getElementById('cfgDbHost').value.trim(),
      port: parseInt(document.getElementById('cfgDbPort').value, 10) || 3306,
      username: document.getElementById('cfgDbUser').value,
      password: document.getElementById('cfgDbPass').value,
      database: document.getElementById('cfgDbName').value,
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
    const modal = document.getElementById('settingsModal');
    if (modal) modal.style.display = 'none';
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
      const bedText = (result.bed_dep && result.bed_dep.total_sent > 0) 
        ? `${result.bed_dep.total_sent} แผนก` 
        : 'เรียบร้อย';
      alert(`✅ ${result.message}\n- เตียง: ส่งสำเร็จ ${bedText}\n- IPD: ส่งสำเร็จ ${result.ipd?.total_sent || 0} รายการ\n- OPD: ส่งสำเร็จ ${result.opd?.total_sent || 0} รายการ\n- Refer: ส่งสำเร็จ ${result.refer?.total_sent || 0} รายการ\n- ผ่าตัด (Operation): ส่งสำเร็จ ${result.operation?.total_sent || 0} รายการ`);
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
