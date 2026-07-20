/**
 * ==============================================================================
 * SISTEM MANAJEMEN SISWA LES PRIVAT CODING
 * Google Apps Script Automation & Web App CRUD Backend
 * ==============================================================================
 */

// Global Constants - Nama Sheet
const SHEET_DASHBOARD = "Dashboard";
const SHEET_MASTER = "Data_Master_Siswa";
const SHEET_LOG = "Log_Pertemuan";
const SHEET_KEUANGAN = "Keuangan_Pembayaran";

/**
 * ------------------------------------------------------------------------------
 * 1. WEB APP & DIALOG ENTRY POINTS
 * ------------------------------------------------------------------------------
 */

/**
 * Web App Entry Point: Membuka Web App Standalone dari URL Web App
 */
function doGet(e) {
  return HtmlService.createHtmlOutputFromFile('Index')
    .setTitle('Sistem Manajemen Les Privat Coding')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL)
    .addMetaTag('viewport', 'width=device-width, initial-scale=1');
}

/**
 * Membuka Web App Interface di dalam Modal Dialog Google Sheets
 */
function openWebAppDialog() {
  const html = HtmlService.createHtmlOutputFromFile('Index')
    .setWidth(1100)
    .setHeight(750);
  SpreadsheetApp.getUi().showModalDialog(html, '🖥️ Aplikasi Web Manajemen Les Privat Coding');
}

/**
 * Trigger saat Spreadsheet dibuka: Menambahkan Menu Kustom ke UI Google Sheets
 */
function onOpen() {
  const ui = SpreadsheetApp.getUi();
  ui.createMenu('🛠️ Coding Class Menu')
    .addItem('🌐 Buka Web App Management', 'openWebAppDialog')
    .addSeparator()
    .addItem('⚡ Setup Initial Spreadsheet', 'setupSpreadsheet')
    .addItem('🔄 Sinkronisasi Dropdown Siswa', 'updateStudentDropdowns')
    .addItem('📊 Hitung Ulang Sisa Kuota Paket', 'manualSyncQuota')
    .addItem('✉️ Buat Draft Pengingat Pembayaran', 'sendPaymentReminders')
    .addToUi();
}

/**
 * Trigger Otomatis: Dijalankan setiap kali ada sel yang diedit di spreadsheet
 */
function onEdit(e) {
  if (!e || !e.source || !e.range) return;
  
  const sheet = e.source.getActiveSheet();
  const range = e.range;
  const sheetName = sheet.getName();
  const row = range.getRow();
  const col = range.getColumn();
  
  if (sheetName === SHEET_MASTER && col === 2 && row > 1) {
    generateStudentId(sheet, range);
    updateStudentDropdowns();
  }
  
  if (sheetName === SHEET_LOG && col === 3 && row > 1) {
    autoFillStudentIdInLog(sheet, row, range.getValue());
  }

  if (sheetName === SHEET_LOG && col === 9 && row > 1) {
    updatePackageQuota(e.source);
  }

  if (sheetName === SHEET_MASTER && col === 9 && row > 1) {
    updateStudentDropdowns();
  }
}

/**
 * ------------------------------------------------------------------------------
 * 2. BACKEND API FOR WEB APP CRUD (google.script.run)
 * ------------------------------------------------------------------------------
 */

/**
 * Ambil data ringkasan untuk Dashboard Web App
 */
function getDashboardData() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const masterSheet = ss.getSheetByName(SHEET_MASTER);
  const logSheet = ss.getSheetByName(SHEET_LOG);
  const keuSheet = ss.getSheetByName(SHEET_KEUANGAN);
  
  let activeStudentsCount = 0;
  let monthlyIncome = 0;
  let unpaidCount = 0;
  let totalSessions = 0;
  let unpaidList = [];
  
  // Master data active count & contact map
  let contactMap = {};
  if (masterSheet && masterSheet.getLastRow() >= 2) {
    const masterVals = masterSheet.getRange(2, 1, masterSheet.getLastRow() - 1, 9).getValues();
    for (let i = 0; i < masterVals.length; i++) {
      let name = masterVals[i][1];
      let contact = masterVals[i][3];
      let status = masterVals[i][8];
      if (status === "Aktif") activeStudentsCount++;
      if (name && contact) contactMap[name] = contact.toString();
    }
  }
  
  // Log count
  if (logSheet && logSheet.getLastRow() >= 2) {
    totalSessions = logSheet.getLastRow() - 1;
  }
  
  // Keuangan stats
  if (keuSheet && keuSheet.getLastRow() >= 2) {
    const keuVals = keuSheet.getRange(2, 1, keuSheet.getLastRow() - 1, 6).getValues();
    const now = new Date();
    const currentMonth = now.getMonth();
    const currentYear = now.getFullYear();
    
    for (let k = 0; k < keuVals.length; k++) {
      let tgl = keuVals[k][0] instanceof Date ? keuVals[k][0] : new Date(keuVals[k][0]);
      let name = keuVals[k][1];
      let periode = keuVals[k][2];
      let nominal = Number(keuVals[k][3]) || 0;
      let statusBayar = keuVals[k][5];
      
      if (statusBayar === "Lunas") {
        if (tgl && tgl.getMonth() === currentMonth && tgl.getFullYear() === currentYear) {
          monthlyIncome += nominal;
        }
      } else if (statusBayar === "Belum Bayar") {
        unpaidCount++;
        unpaidList.push({
          row: k + 2,
          date: tgl ? formatDate(tgl) : "",
          name: name || "-",
          periode: periode || "-",
          nominal: nominal,
          contact: contactMap[name] || "Kosong"
        });
      }
    }
  }
  
  return {
    activeStudentsCount: activeStudentsCount,
    monthlyIncome: monthlyIncome,
    unpaidCount: unpaidCount,
    totalSessions: totalSessions,
    unpaidList: unpaidList
  };
}

/**
 * CRUD: Get All Students
 */
function getStudents() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sheet = ss.getSheetByName(SHEET_MASTER);
  if (!sheet || sheet.getLastRow() < 2) return [];
  
  const values = sheet.getRange(2, 1, sheet.getLastRow() - 1, 9).getValues();
  let students = [];
  
  for (let i = 0; i < values.length; i++) {
    if (values[i][1]) { // If student name exists
      students.push({
        row: i + 2,
        id: values[i][0] ? values[i][0].toString() : "",
        name: values[i][1] ? values[i][1].toString() : "",
        age: values[i][2] ? values[i][2].toString() : "",
        contact: values[i][3] ? values[i][3].toString() : "",
        language: values[i][4] ? values[i][4].toString() : "",
        schedule: values[i][5] ? values[i][5].toString() : "",
        system: values[i][6] ? values[i][6].toString() : "",
        quota: values[i][7] !== undefined ? values[i][7] : "",
        status: values[i][8] ? values[i][8].toString() : "Aktif"
      });
    }
  }
  return students;
}

/**
 * CRUD: Save (Create/Update) Student
 */
function saveStudent(data) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sheet = ss.getSheetByName(SHEET_MASTER);
  if (!sheet) throw new Error("Sheet Data_Master_Siswa tidak ditemukan!");
  
  let row = Number(data.row);
  let studentId = data.id;
  
  if (!row) { // New Student
    row = sheet.getLastRow() + 1;
    // Auto-generate ID if empty
    if (!studentId) {
      let maxId = 0;
      if (sheet.getLastRow() >= 2) {
        const ids = sheet.getRange(2, 1, sheet.getLastRow() - 1, 1).getValues();
        for (let i = 0; i < ids.length; i++) {
          let str = ids[i][0] ? ids[i][0].toString() : "";
          if (str.startsWith("COD-")) {
            let num = parseInt(str.replace("COD-", ""), 10);
            if (!isNaN(num) && num > maxId) maxId = num;
          }
        }
      }
      studentId = "COD-" + String(maxId + 1).padStart(3, '0');
    }
  }
  
  let quotaVal = data.system === "Paket" ? (Number(data.quota) || 8) : "N/A (Bulanan)";
  
  const rowData = [
    studentId,
    data.name,
    data.age,
    data.contact,
    data.language,
    data.schedule,
    data.system,
    quotaVal,
    data.status || "Aktif"
  ];
  
  sheet.getRange(row, 1, 1, rowData.length).setValues([rowData]);
  updateStudentDropdowns();
  updatePackageQuota(ss);
  return { success: true, message: "Data Siswa berhasil disimpan!", id: studentId };
}

/**
 * CRUD: Delete Student
 */
function deleteStudent(row) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sheet = ss.getSheetByName(SHEET_MASTER);
  if (!sheet) throw new Error("Sheet tidak ditemukan!");
  
  if (row >= 2 && row <= sheet.getLastRow()) {
    sheet.deleteRow(row);
    updateStudentDropdowns();
    return { success: true, message: "Data Siswa berhasil dihapus!" };
  }
  throw new Error("Baris data tidak valid!");
}

/**
 * CRUD: Get All Logs
 */
function getLogs() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sheet = ss.getSheetByName(SHEET_LOG);
  if (!sheet || sheet.getLastRow() < 2) return [];
  
  const values = sheet.getRange(2, 1, sheet.getLastRow() - 1, 9).getValues();
  let logs = [];
  
  for (let i = 0; i < values.length; i++) {
    if (values[i][2]) { // If student name exists
      let tgl = values[i][0] instanceof Date ? values[i][0] : new Date(values[i][0]);
      logs.push({
        row: i + 2,
        date: tgl ? formatDate(tgl, true) : "",
        rawDateIso: tgl ? formatIsoLocal(tgl) : "",
        studentId: values[i][1] ? values[i][1].toString() : "",
        studentName: values[i][2] ? values[i][2].toString() : "",
        sessionNum: values[i][3] || 1,
        topic: values[i][4] ? values[i][4].toString() : "",
        progress: values[i][5] ? values[i][5].toString() : "",
        rating: values[i][6] || 5,
        notes: values[i][7] ? values[i][7].toString() : "",
        status: values[i][8] ? values[i][8].toString() : "Hadir"
      });
    }
  }
  return logs.reverse(); // Newest first
}

/**
 * CRUD: Save Log
 */
function saveLog(data) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sheet = ss.getSheetByName(SHEET_LOG);
  if (!sheet) throw new Error("Sheet Log_Pertemuan tidak ditemukan!");
  
  let row = Number(data.row);
  if (!row) {
    row = sheet.getLastRow() + 1;
  }
  
  // Find Student ID
  let studentId = data.studentId;
  if (!studentId && data.studentName) {
    const masterSheet = ss.getSheetByName(SHEET_MASTER);
    if (masterSheet && masterSheet.getLastRow() >= 2) {
      const mVals = masterSheet.getRange(2, 1, masterSheet.getLastRow() - 1, 2).getValues();
      for (let m = 0; m < mVals.length; m++) {
        if (mVals[m][1] === data.studentName) {
          studentId = mVals[m][0];
          break;
        }
      }
    }
  }
  
  let logDate = data.date ? new Date(data.date) : new Date();
  
  const rowData = [
    logDate,
    studentId || "",
    data.studentName,
    Number(data.sessionNum) || 1,
    data.topic || "",
    data.progress || "",
    Number(data.rating) || 5,
    data.notes || "",
    data.status || "Hadir"
  ];
  
  sheet.getRange(row, 1, 1, rowData.length).setValues([rowData]);
  updatePackageQuota(ss);
  return { success: true, message: "Log Pertemuan berhasil disimpan!" };
}

/**
 * CRUD: Delete Log
 */
function deleteLog(row) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sheet = ss.getSheetByName(SHEET_LOG);
  if (!sheet) throw new Error("Sheet tidak ditemukan!");
  
  if (row >= 2 && row <= sheet.getLastRow()) {
    sheet.deleteRow(row);
    updatePackageQuota(ss);
    return { success: true, message: "Log Pertemuan berhasil dihapus!" };
  }
  throw new Error("Baris data tidak valid!");
}

/**
 * CRUD: Get All Payments
 */
function getPayments() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sheet = ss.getSheetByName(SHEET_KEUANGAN);
  if (!sheet || sheet.getLastRow() < 2) return [];
  
  const values = sheet.getRange(2, 1, sheet.getLastRow() - 1, 6).getValues();
  let payments = [];
  
  for (let i = 0; i < values.length; i++) {
    if (values[i][1]) {
      let tgl = values[i][0] instanceof Date ? values[i][0] : new Date(values[i][0]);
      payments.push({
        row: i + 2,
        date: tgl ? formatDate(tgl) : "",
        studentName: values[i][1] ? values[i][1].toString() : "",
        periode: values[i][2] ? values[i][2].toString() : "",
        nominal: Number(values[i][3]) || 0,
        method: values[i][4] ? values[i][4].toString() : "BCA",
        status: values[i][5] ? values[i][5].toString() : "Belum Bayar"
      });
    }
  }
  return payments.reverse(); // Newest first
}

/**
 * CRUD: Save Payment
 */
function savePayment(data) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sheet = ss.getSheetByName(SHEET_KEUANGAN);
  if (!sheet) throw new Error("Sheet Keuangan_Pembayaran tidak ditemukan!");
  
  let row = Number(data.row);
  if (!row) {
    row = sheet.getLastRow() + 1;
  }
  
  let payDate = data.date ? new Date(data.date) : new Date();
  
  const rowData = [
    payDate,
    data.studentName,
    data.periode || "",
    Number(data.nominal) || 0,
    data.method || "BCA",
    data.status || "Belum Bayar"
  ];
  
  sheet.getRange(row, 1, 1, rowData.length).setValues([rowData]);
  updatePackageQuota(ss);
  return { success: true, message: "Transaksi Pembayaran berhasil disimpan!" };
}

/**
 * CRUD: Delete Payment
 */
function deletePayment(row) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sheet = ss.getSheetByName(SHEET_KEUANGAN);
  if (!sheet) throw new Error("Sheet tidak ditemukan!");
  
  if (row >= 2 && row <= sheet.getLastRow()) {
    sheet.deleteRow(row);
    updatePackageQuota(ss);
    return { success: true, message: "Transaksi Pembayaran berhasil dihapus!" };
  }
  throw new Error("Baris data tidak valid!");
}

/**
 * Helper: Format Tanggal ke String (YYYY-MM-DD)
 */
function formatDate(dateObj, includeTime = false) {
  if (!(dateObj instanceof Date) || isNaN(dateObj.getTime())) return "";
  let year = dateObj.getFullYear();
  let month = String(dateObj.getMonth() + 1).padStart(2, '0');
  let day = String(dateObj.getDate()).padStart(2, '0');
  let str = `${year}-${month}-${day}`;
  if (includeTime) {
    let hours = String(dateObj.getHours()).padStart(2, '0');
    let mins = String(dateObj.getMinutes()).padStart(2, '0');
    str += ` ${hours}:${mins}`;
  }
  return str;
}

function formatIsoLocal(dateObj) {
  if (!(dateObj instanceof Date) || isNaN(dateObj.getTime())) return "";
  let year = dateObj.getFullYear();
  let month = String(dateObj.getMonth() + 1).padStart(2, '0');
  let day = String(dateObj.getDate()).padStart(2, '0');
  let hours = String(dateObj.getHours()).padStart(2, '0');
  let mins = String(dateObj.getMinutes()).padStart(2, '0');
  return `${year}-${month}-${day}T${hours}:${mins}`;
}

/**
 * ------------------------------------------------------------------------------
 * 3. OTOMATISASI EXISTING & HELPER FUNCTIONS
 * ------------------------------------------------------------------------------
 */

function generateStudentId(sheet, editedRange) {
  const row = editedRange.getRow();
  const idCell = sheet.getRange(row, 1);
  const studentName = editedRange.getValue();
  
  if (idCell.getValue() === "" && studentName !== "") {
    const lastRow = sheet.getLastRow();
    let maxId = 0;
    
    if (lastRow > 2) {
      const idValues = sheet.getRange(2, 1, lastRow - 1, 1).getValues();
      for (let i = 0; i < idValues.length; i++) {
        let currentIdStr = idValues[i][0] ? idValues[i][0].toString() : "";
        if (currentIdStr.startsWith("COD-")) {
          let num = parseInt(currentIdStr.replace("COD-", ""), 10);
          if (!isNaN(num) && num > maxId) maxId = num;
        }
      }
    }
    
    const nextId = "COD-" + String(maxId + 1).padStart(3, '0');
    idCell.setValue(nextId);
  }
}

function autoFillStudentIdInLog(logSheet, row, selectedName) {
  if (!selectedName) return;
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const masterSheet = ss.getSheetByName(SHEET_MASTER);
  if (!masterSheet || masterSheet.getLastRow() < 2) return;
  
  const masterData = masterSheet.getRange(2, 1, masterSheet.getLastRow() - 1, 2).getValues();
  let foundId = "";
  for (let i = 0; i < masterData.length; i++) {
    if (masterData[i][1] === selectedName) {
      foundId = masterData[i][0];
      break;
    }
  }
  if (foundId) logSheet.getRange(row, 2).setValue(foundId);
}

function updatePackageQuota(spreadsheet) {
  const ss = spreadsheet || SpreadsheetApp.getActiveSpreadsheet();
  const masterSheet = ss.getSheetByName(SHEET_MASTER);
  const logSheet = ss.getSheetByName(SHEET_LOG);
  const keuanganSheet = ss.getSheetByName(SHEET_KEUANGAN);
  
  if (!masterSheet || masterSheet.getLastRow() < 2) return;
  
  const masterRange = masterSheet.getRange(2, 1, masterSheet.getLastRow() - 1, 9);
  const masterValues = masterRange.getValues();
  
  let attendanceMap = {};
  if (logSheet && logSheet.getLastRow() >= 2) {
    const logValues = logSheet.getRange(2, 3, logSheet.getLastRow() - 1, 7).getValues();
    for (let i = 0; i < logValues.length; i++) {
      let name = logValues[i][0];
      let status = logValues[i][6];
      if (name && status === "Hadir") {
        attendanceMap[name] = (attendanceMap[name] || 0) + 1;
      }
    }
  }
  
  let packageBoughtMap = {};
  if (keuanganSheet && keuanganSheet.getLastRow() >= 2) {
    const keuValues = keuanganSheet.getRange(2, 2, keuanganSheet.getLastRow() - 1, 5).getValues();
    for (let k = 0; k < keuValues.length; k++) {
      let name = keuValues[k][0];
      let itemInfo = keuValues[k][1];
      let statusBayar = keuValues[k][4];
      
      if (name && statusBayar === "Lunas") {
        let sessionsBought = 8;
        if (itemInfo) {
          let match = itemInfo.toString().match(/(\d+)\s*sesi/i);
          if (match && match[1]) {
            sessionsBought = parseInt(match[1], 10);
          }
        }
        packageBoughtMap[name] = (packageBoughtMap[name] || 0) + sessionsBought;
      }
    }
  }

  let updatedQuotaValues = [];
  for (let j = 0; j < masterValues.length; j++) {
    let name = masterValues[j][1];
    let sistemBelajar = masterValues[j][6];
    let currentQuota = masterValues[j][7];
    
    if (sistemBelajar === "Paket") {
      let totalHadir = attendanceMap[name] || 0;
      let totalBought = packageBoughtMap[name];
      let finalRemaining;
      if (totalBought !== undefined) {
        finalRemaining = Math.max(0, totalBought - totalHadir);
      } else {
        finalRemaining = typeof currentQuota === 'number' ? currentQuota : 8;
      }
      updatedQuotaValues.push([finalRemaining]);
    } else {
      updatedQuotaValues.push(["N/A (Bulanan)"]);
    }
  }
  
  masterSheet.getRange(2, 8, updatedQuotaValues.length, 1).setValues(updatedQuotaValues);
}

function manualSyncQuota() {
  updatePackageQuota();
  SpreadsheetApp.getUi().alert('✅ Kalkulasi dan pembaruan kuota sisa sesi paket berhasil dijalankan!');
}

function updateStudentDropdowns() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const masterSheet = ss.getSheetByName(SHEET_MASTER);
  const logSheet = ss.getSheetByName(SHEET_LOG);
  const keuanganSheet = ss.getSheetByName(SHEET_KEUANGAN);
  
  if (!masterSheet || masterSheet.getLastRow() < 2) return;
  
  const masterData = masterSheet.getRange(2, 2, masterSheet.getLastRow() - 1, 8).getValues();
  let studentNames = [];
  for (let i = 0; i < masterData.length; i++) {
    let name = masterData[i][0];
    let status = masterData[i][7];
    if (name && status !== "Lulus") {
      studentNames.push(name);
    }
  }
  
  if (studentNames.length === 0) return;
  
  const rule = SpreadsheetApp.newDataValidation()
    .requireValueInList(studentNames, true)
    .setAllowInvalid(false)
    .build();
    
  if (logSheet) logSheet.getRange("C2:C500").setDataValidation(rule);
  if (keuanganSheet) keuanganSheet.getRange("B2:B500").setDataValidation(rule);
}

function sendPaymentReminders() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const keuanganSheet = ss.getSheetByName(SHEET_KEUANGAN);
  const masterSheet = ss.getSheetByName(SHEET_MASTER);
  const ui = SpreadsheetApp.getUi();
  
  if (!keuanganSheet || !masterSheet) {
    if (ui) ui.alert('⚠️ Sheet Keuangan_Pembayaran atau Data_Master_Siswa tidak ditemukan.');
    return;
  }
  
  const keuLastRow = keuanganSheet.getLastRow();
  if (keuLastRow < 2) {
    if (ui) ui.alert('ℹ️ Belum ada data transaksi pembayaran.');
    return;
  }
  
  const masterLastRow = masterSheet.getLastRow();
  let contactMap = {};
  if (masterLastRow >= 2) {
    const masterValues = masterSheet.getRange(2, 2, masterLastRow - 1, 3).getValues();
    for (let m = 0; m < masterValues.length; m++) {
      let name = masterValues[m][0];
      let contact = masterValues[m][2];
      if (name && contact) contactMap[name] = contact.toString().trim();
    }
  }
  
  const keuValues = keuanganSheet.getRange(2, 1, keuLastRow - 1, 6).getValues();
  let draftCount = 0;
  let skippedList = [];
  
  for (let i = 0; i < keuValues.length; i++) {
    let namaSiswa = keuValues[i][1];
    let periode = keuValues[i][2];
    let nominal = keuValues[i][3];
    let statusBayar = keuValues[i][5];
    
    if (namaSiswa && statusBayar === "Belum Bayar") {
      let recipientEmail = contactMap[namaSiswa];
      if (recipientEmail && recipientEmail.includes("@")) {
        let subject = `[Pengingat Pembayaran] Les Privat Coding - ${namaSiswa} (${periode})`;
        let formattedNominal = typeof nominal === 'number' ? "Rp " + nominal.toLocaleString('id-ID') : nominal;
        
        let body = `Halo Bapak/Ibu,\n\n` +
          `Semoga sehat selalu. Ini adalah pesan pengingat resmi untuk tagihan les privat coding atas nama:\n\n` +
          `• Nama Siswa: ${namaSiswa}\n` +
          `• Periode / Paket: ${periode}\n` +
          `• Total Tagihan: ${formattedNominal}\n\n` +
          `Mohon untuk melakukan pembayaran dan mengonfirmasi bukti transfer.\n\n` +
          `Terima kasih atas perhatian dan kerja samanya.\n\n` +
          `Salam hangat,\n` +
          `Pengajar Les Privat Coding`;
          
        GmailApp.createDraft(recipientEmail, subject, body);
        draftCount++;
      } else {
        skippedList.push(`${namaSiswa} (Kontak '${recipientEmail || "Kosong"}' bukan email)`);
      }
    }
  }
  
  let msg = `✅ Berhasil membuat ${draftCount} Draft Email di Gmail Anda!`;
  if (skippedList.length > 0) {
    msg += `\n\n⚠️ ${skippedList.length} siswa dilewati karena kontak bukan format email:\n- ` + skippedList.join('\n- ');
  }
  
  if (ui) ui.alert('Hasil Buat Draft Pengingat', msg, ui.ButtonSet.OK);
  return { draftCount: draftCount, message: msg };
}

/**
 * ------------------------------------------------------------------------------
 * 4. INITIAL SETUP SPREADSHEET
 * ------------------------------------------------------------------------------
 */

function setupSpreadsheet() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const ui = SpreadsheetApp.getUi();
  
  if (ui) {
    const confirm = ui.alert(
      'Konfirmasi Setup Initial',
      'Fungsi ini akan membuat dan menata 4 sheet utama (Dashboard, Data_Master_Siswa, Log_Pertemuan, Keuangan_Pembayaran).\nLanjutkan?',
      ui.ButtonSet.YES_NO
    );
    if (confirm !== ui.Button.YES) return;
  }
  
  let masterSheet = getOrCreateSheet(ss, SHEET_MASTER);
  setupMasterSheet(masterSheet);
  
  let logSheet = getOrCreateSheet(ss, SHEET_LOG);
  setupLogSheet(logSheet);
  
  let keuSheet = getOrCreateSheet(ss, SHEET_KEUANGAN);
  setupKeuanganSheet(keuSheet);
  
  let dashSheet = getOrCreateSheet(ss, SHEET_DASHBOARD);
  setupDashboardSheet(dashSheet);
  
  ss.setActiveSheet(dashSheet);
  ss.moveActiveSheet(1);
  updateStudentDropdowns();
  
  if (ui) ui.alert('🎉 Setup Berhasil!', 'Seluruh sheet, struktur data, format, dan rumus dashboard telah siap digunakan.', ui.ButtonSet.OK);
}

function getOrCreateSheet(ss, name) {
  let sheet = ss.getSheetByName(name);
  if (!sheet) sheet = ss.insertSheet(name);
  return sheet;
}

function setupMasterSheet(sheet) {
  sheet.clear();
  sheet.setHiddenGridlines(false);
  const headers = [["ID_Siswa", "Nama_Siswa", "Usia_Tingkat", "Kontak_Ortu", "Bahasa_Pemrograman", "Catatan_Jadwal", "Sistem_Belajar", "Sisa_Sesi_Paket", "Status"]];
  sheet.getRange(1, 1, 1, headers[0].length).setValues(headers);
  applyHeaderStyle(sheet.getRange(1, 1, 1, headers[0].length));
  
  const valProg = SpreadsheetApp.newDataValidation().requireValueInList(["Python", "Web Dev (HTML/CSS/JS)", "Scratch", "Java", "C++", "Mobile App (Flutter/React Native)", "Data Science", "Lainnya"], true).build();
  sheet.getRange("E2:E500").setDataValidation(valProg);
  
  const valSistem = SpreadsheetApp.newDataValidation().requireValueInList(["Paket", "Bulanan"], true).build();
  sheet.getRange("G2:G500").setDataValidation(valSistem);
  
  const valStatus = SpreadsheetApp.newDataValidation().requireValueInList(["Aktif", "Cuti", "Lulus"], true).build();
  sheet.getRange("I2:I500").setDataValidation(valStatus);
  
  const widths = [100, 180, 110, 180, 190, 150, 130, 130, 100];
  widths.forEach((w, colIdx) => sheet.setColumnWidth(colIdx + 1, w));
  sheet.setFrozenRows(1);
  
  sheet.getRange("A2:I3").setValues([
    ["COD-001", "Budi Santoso", "SMP Kelas 8", "budi.ortu@email.com", "Python", "Fleksibel", "Paket", 8, "Aktif"],
    ["COD-002", "Siti Rahma", "SMA Kelas 10", "siti.ortu@email.com", "Web Dev (HTML/CSS/JS)", "Fleksibel", "Bulanan", "N/A (Bulanan)", "Aktif"]
  ]);
}

function setupLogSheet(sheet) {
  sheet.clear();
  sheet.setHiddenGridlines(false);
  const headers = [["Timestamp / Tanggal", "ID_Siswa", "Nama_Siswa", "Pertemuan_Ke", "Materi_Coding", "Progress_Project", "Rating_Pemahaman", "Catatan_Evaluasi", "Status_Kehadiran"]];
  sheet.getRange(1, 1, 1, headers[0].length).setValues(headers);
  applyHeaderStyle(sheet.getRange(1, 1, 1, headers[0].length));
  
  const valRating = SpreadsheetApp.newDataValidation().requireValueInList([1, 2, 3, 4, 5], true).build();
  sheet.getRange("G2:G500").setDataValidation(valRating);
  
  const valHadir = SpreadsheetApp.newDataValidation().requireValueInList(["Hadir", "Izin", "Alfa"], true).build();
  sheet.getRange("I2:I500").setDataValidation(valHadir);
  
  sheet.getRange("A2:A500").setNumberFormat("yyyy-mm-dd hh:mm");
  const widths = [140, 100, 180, 110, 220, 220, 130, 220, 120];
  widths.forEach((w, colIdx) => sheet.setColumnWidth(colIdx + 1, w));
  sheet.setFrozenRows(1);
  
  sheet.getRange("A2:I2").setValues([
    [new Date(), "COD-001", "Budi Santoso", 1, "Pengenalan Variable & Data Type Python", "Membuat Kalkulator Sederhana", 4, "Sangat antusias, cepat paham", "Hadir"]
  ]);
}

function setupKeuanganSheet(sheet) {
  sheet.clear();
  sheet.setHiddenGridlines(false);
  const headers = [["Tanggal_Bayar", "Nama_Siswa", "Periode_Paket", "Nominal", "Metode_Transfer", "Status_Bayar"]];
  sheet.getRange(1, 1, 1, headers[0].length).setValues(headers);
  applyHeaderStyle(sheet.getRange(1, 1, 1, headers[0].length));
  
  const valMetode = SpreadsheetApp.newDataValidation().requireValueInList(["BCA", "Mandiri", "BNI", "BRI", "E-Wallet (GoPay/OVO/Dana)", "Cash", "Lainnya"], true).build();
  sheet.getRange("E2:E500").setDataValidation(valMetode);
  
  const valStatusBayar = SpreadsheetApp.newDataValidation().requireValueInList(["Lunas", "Belum Bayar"], true).build();
  sheet.getRange("F2:F500").setDataValidation(valStatusBayar);
  
  sheet.getRange("A2:A500").setNumberFormat("yyyy-mm-dd");
  sheet.getRange("D2:D500").setNumberFormat('"Rp"#,,##0');
  const widths = [130, 180, 160, 140, 180, 130];
  widths.forEach((w, colIdx) => sheet.setColumnWidth(colIdx + 1, w));
  sheet.setFrozenRows(1);
  
  sheet.getRange("A2:F3").setValues([
    [new Date(), "Budi Santoso", "Paket 8 Sesi", 1200000, "BCA", "Lunas"],
    [new Date(), "Siti Rahma", "Bulan Juli 2026", 600000, "Mandiri", "Belum Bayar"]
  ]);
}

function setupDashboardSheet(sheet) {
  sheet.clear();
  sheet.setHiddenGridlines(false);
  
  sheet.getRange("B2:F2").merge();
  sheet.getRange("B2").setValue("🖥️ DASHBOARD SISTEM MANAJEMEN LES PRIVAT CODING");
  sheet.getRange("B2").setFontSize(14).setFontWeight("bold").setFontColor("#1e293b").setHorizontalAlignment("center");
  
  sheet.getRange("B3:F3").merge();
  sheet.getRange("B3").setValue("Ringkasan Metrik Bisnis & Keaktifan Siswa");
  sheet.getRange("B3").setFontSize(10).setFontStyle("italic").setFontColor("#64748b").setHorizontalAlignment("center");
  
  setupScorecardCard(sheet, "B5", "C5", "B6", "C6", "TOTAL SISWA AKTIF", '=COUNTA(IFERROR(FILTER(Data_Master_Siswa!A2:A; Data_Master_Siswa!I2:I="Aktif"); ""))', "#3b82f6");
  setupScorecardCard(sheet, "D5", "E5", "D6", "E6", "PENDAPATAN BULAN INI", '=SUMIFS(Keuangan_Pembayaran!D2:D; Keuangan_Pembayaran!F2:F="Lunas"; Keuangan_Pembayaran!A2:A; ">="&DATE(YEAR(TODAY()); MONTH(TODAY()); 1))', "#10b981", true);
  setupScorecardCard(sheet, "F5", "F5", "F6", "F6", "BELUM BAYAR", '=COUNTIF(Keuangan_Pembayaran!F2:F; "Belum Bayar")', "#ef4444");
  
  sheet.getRange("B9:F9").merge();
  sheet.getRange("B9").setValue("⚠️ DAFTAR SISWA MENUNGGU PEMBAYARAN");
  sheet.getRange("B9").setFontWeight("bold").setFontColor("#991b1b").setBackground("#fee2e2");
  
  const subHeaders = [["Nama Siswa", "Periode / Paket", "Nominal Tagihan", "Status", "Kontak Email Ortu"]];
  sheet.getRange("B10:F10").setValues(subHeaders);
  applySubHeaderStyle(sheet.getRange("B10:F10"));
  
  sheet.getRange("B11").setFormula(
    '=IFERROR(FILTER({Keuangan_Pembayaran!B2:C\\ Keuangan_Pembayaran!D2:D\\ Keuangan_Pembayaran!F2:F\\ XLOOKUP(Keuangan_Pembayaran!B2:B; Data_Master_Siswa!B2:B; Data_Master_Siswa!D2:D)}; Keuangan_Pembayaran!F2:F="Belum Bayar"); "Tidak Ada Tagihan Belum Bayar")'
  );
  
  sheet.setColumnWidth(1, 30);
  sheet.setColumnWidth(2, 180);
  sheet.setColumnWidth(3, 160);
  sheet.setColumnWidth(4, 150);
  sheet.setColumnWidth(5, 130);
  sheet.setColumnWidth(6, 200);
  sheet.getRange("D11:D30").setNumberFormat('"Rp"#,,##0');
}

function setupScorecardCard(sheet, labelStart, labelEnd, valStart, valEnd, title, formula, accentColor, isCurrency = false) {
  sheet.getRange(`${labelStart}:${labelEnd}`).merge();
  sheet.getRange(labelStart).setValue(title).setFontSize(9).setFontWeight("bold").setFontColor("#475569").setHorizontalAlignment("center").setBackground("#f1f5f9");
  
  sheet.getRange(`${valStart}:${valEnd}`).merge();
  sheet.getRange(valStart).setFormula(formula).setFontSize(16).setFontWeight("bold").setFontColor(accentColor).setHorizontalAlignment("center").setBackground("#ffffff");
  
  if (isCurrency) sheet.getRange(valStart).setNumberFormat('"Rp"#,,##0');
  sheet.getRange(`${labelStart}:${valEnd}`).setBorder(true, true, true, true, false, false, "#cbd5e1", SpreadsheetApp.BorderStyle.SOLID);
}

function applyHeaderStyle(range) {
  range.setBackground("#1e293b").setFontColor("#ffffff").setFontWeight("bold").setHorizontalAlignment("center").setVerticalAlignment("middle");
}

function applySubHeaderStyle(range) {
  range.setBackground("#334155").setFontColor("#ffffff").setFontWeight("bold").setHorizontalAlignment("center");
}
