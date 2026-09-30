// ============================================================
// 🚀 CSSI PORTAL — Google Apps Script COMPLET
// ============================================================
// 
// INSTRUCȚIUNI INSTALARE:
// 1. Deschide Google Sheets (oricare din cele 5)
// 2. Extensions → Apps Script
// 3. Șterge conținutul existent
// 4. Copiază tot acest fișier și dă paste
// 5. Click 💾 Save
// 6. Run → "installTriggers" (prima dată)
// 7. Autorizează accesul când ți se cere
//
// ============================================================

// === CONFIGURARE (modifică aici) ===
const CONFIG = {
  // Emailuri notificări
  EMAIL_ADMIN: 'cssirobv@gmail.com',
  EMAIL_OFFICE: 'office@breaksistems.ro', // adaugă dacă e diferit
  
  // Sheet IDs
  SHEET_CRM: '1DQlmxJWMQh9NpwzKNwti2YN47u2vKIRG2n9Q1h2rVHk',
  SHEET_SOCIAL: '15gVARAiR_MRFJsxPJ-d4QfY4lRlsafTBaCIlw8AzPL8',
  SHEET_CHECKLIST: '1JoxHEsk3FZ1I_sUKbWC0NoipS8RziWKZeNmRiLCc-0g',
  SHEET_MATERIALE: '11QVsJJxocDYZ_nAMbPKqU_ZNZbUM4gdAGgX9iVbDUpI',
  SHEET_CALCULATOR: '18Wkkvdg7hpYHCkufvCNkxbGWv0BS6epzNelD2IWGWfw',
  
  // Form IDs (for responses)
  FORM_SOCIAL: '1FAIpQLSfhA6FFeC2XYVf0rBrPFeeKcPLgRtRbgc-8PMLpq8DAYBw2Zw',
  FORM_CHECKLIST: '1FAIpQLSf-4Py6K9BUzgBg3FqY1pwFI49HMIHKnkh1tiVD4jeC2Ddlqw',
  FORM_MATERIALE: '1FAIpQLSc0UNP0EbNjwmX9G0Kz8iFeHMXI9_4n1QoWzg4mSr6IVxwymg',
  FORM_CALENDAR: '1FAIpQLSfrveukGmpvZdthcpl_xq90SsLoeAf8VP6_KzLDpa-WQxMjhA',
};


// ============================================================
// 1. NOTIFICĂRI EMAIL LA COMPLETARE FORMULAR
// ============================================================

/**
 * Se apelează automat când cineva completează un formular.
 * Trimite email cu rezumatul datelor la admin.
 */
function onFormSubmit(e) {
  try {
    const sheet = e.source.getActiveSheet();
    const sheetId = e.source.getId();
    const row = e.range.getRow();
    const data = sheet.getRange(row, 1, 1, sheet.getLastColumn()).getValues()[0];
    const headers = sheet.getRange(1, 1, 1, sheet.getLastColumn()).getValues()[0];
    
    let subject = '';
    let body = '';
    let urgent = false;
    
    // Detectează tipul formularului
    if (sheetId === CONFIG.SHEET_CHECKLIST || sheetId.includes('Checklist')) {
      subject = '✅ Raport Montaj Nou · CSSI Portal';
      body = '🔧 Un tehnician a completat un raport de montaj.\n\n';
    } else if (sheetId === CONFIG.SHEET_MATERIALE || sheetId.includes('Material')) {
      subject = '📦 Solicitare Material · CSSI Portal';
      body = '📦 S-a solicitat un material nou.\n\n';
      // Verifică urgență
      const dataStr = data.join(' ').toLowerCase();
      if (dataStr.includes('urgent') || dataStr.includes('🔴')) {
        subject = '🔴 URGENT: Solicitare Material · CSSI Portal';
        urgent = true;
      }
    } else if (sheetId === CONFIG.SHEET_SOCIAL || sheetId.includes('Social')) {
      subject = '🚀 Postare Nouă Programată · CSSI Portal';
      body = '📱 O nouă postare a fost programată.\n\n';
    } else if (sheetId === CONFIG.SHEET_CRM || sheetId.includes('CRM')) {
      subject = '💰 Lead NOU · CSSI Portal';
      body = '📞 Un lead nou a fost adăugat în CRM!\n\n';
      urgent = true;
    } else {
      subject = '📝 Formular Completat · CSSI Portal';
      body = '📋 Un formular a fost completat.\n\n';
    }
    
    // Construiește body-ul emailului
    body += '━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n';
    for (let i = 0; i < headers.length; i++) {
      if (headers[i] && data[i]) {
        body += `📌 ${headers[i]}: ${data[i]}\n`;
      }
    }
    body += '━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n';
    body += `🔗 Vezi în Sheet: https://docs.google.com/spreadsheets/d/${sheetId}/edit\n`;
    body += `🏠 Portal CSSI: https://cssibv.github.io/cssi-website/admin.html\n\n`;
    body += 'Portal CSSI v2.0';
    
    // Trimite email
    MailApp.sendEmail({
      to: CONFIG.EMAIL_ADMIN,
      subject: subject,
      body: body
    });
    
    // Dacă e urgent, trimite și la office
    if (urgent && CONFIG.EMAIL_OFFICE) {
      MailApp.sendEmail({
        to: CONFIG.EMAIL_OFFICE,
        subject: subject,
        body: body
      });
    }
    
    Logger.log('✅ Email notificare trimis: ' + subject);
    
  } catch (error) {
    Logger.log('❌ Eroare notificare: ' + error.toString());
  }
}


// ============================================================
// 2. RAPORT ZILNIC AUTOMAT (trimis la 08:00)
// ============================================================

function dailyReport() {
  try {
    const today = new Date();
    const dateStr = Utilities.formatDate(today, 'Europe/Bucharest', 'dd/MM/yyyy');
    
    let report = `📊 RAPORT ZILNIC CSSI · ${dateStr}\n`;
    report += '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n';
    
    // CRM Stats
    try {
      const crmSheet = SpreadsheetApp.openById(CONFIG.SHEET_CRM).getActiveSheet();
      const crmData = crmSheet.getDataRange().getValues();
      const totalLeads = crmData.length - 1; // minus header
      let newLeads = 0;
      let contactat = 0;
      let castigat = 0;
      
      crmData.forEach((row, i) => {
        if (i === 0) return;
        const status = row.join(' ').toLowerCase();
        if (status.includes('nou') || status.includes('new')) newLeads++;
        if (status.includes('contactat')) contactat++;
        if (status.includes('câștigat') || status.includes('castigat')) castigat++;
      });
      
      report += `💰 CRM CLIENȚI\n`;
      report += `   Total lead-uri: ${totalLeads}\n`;
      report += `   🆕 Noi: ${newLeads} | 📞 Contactați: ${contactat} | ✅ Câștigați: ${castigat}\n\n`;
    } catch(e) {
      report += `💰 CRM: Nu s-a putut accesa\n\n`;
    }
    
    // Materiale Stats
    try {
      const matSheet = SpreadsheetApp.openById(CONFIG.SHEET_MATERIALE).getActiveSheet();
      const matData = matSheet.getDataRange().getValues();
      let pending = 0;
      let urgent = 0;
      
      matData.forEach((row, i) => {
        if (i === 0) return;
        const rowStr = row.join(' ').toLowerCase();
        if (!rowStr.includes('livrat') && !rowStr.includes('completat')) pending++;
        if (rowStr.includes('urgent') || rowStr.includes('🔴')) urgent++;
      });
      
      report += `📦 MATERIALE\n`;
      report += `   În așteptare: ${pending}\n`;
      report += `   🔴 Urgente: ${urgent}\n\n`;
    } catch(e) {
      report += `📦 Materiale: Nu s-a putut accesa\n\n`;
    }
    
    // Checklist Stats
    try {
      const checkSheet = SpreadsheetApp.openById(CONFIG.SHEET_CHECKLIST).getActiveSheet();
      const checkData = checkSheet.getDataRange().getValues();
      const totalRapoarte = checkData.length - 1;
      
      // Rapoarte ultimele 7 zile
      const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
      let thisWeek = 0;
      checkData.forEach((row, i) => {
        if (i === 0) return;
        const d = new Date(row[0]); // presupunem coloana A = timestamp
        if (d >= weekAgo) thisWeek++;
      });
      
      report += `🔧 LUCRĂRI\n`;
      report += `   Total rapoarte: ${totalRapoarte}\n`;
      report += `   Ultimele 7 zile: ${thisWeek}\n\n`;
    } catch(e) {
      report += `🔧 Lucrări: Nu s-a putut accesa\n\n`;
    }
    
    // Social Stats
    try {
      const socialSheet = SpreadsheetApp.openById(CONFIG.SHEET_SOCIAL).getActiveSheet();
      const socialData = socialSheet.getDataRange().getValues();
      let planificat = 0;
      let postat = 0;
      
      socialData.forEach((row, i) => {
        if (i === 0) return;
        const rowStr = row.join(' ').toLowerCase();
        if (rowStr.includes('planificat')) planificat++;
        if (rowStr.includes('postat') || rowStr.includes('✅')) postat++;
      });
      
      report += `🚀 SOCIAL MEDIA\n`;
      report += `   📝 Planificate: ${planificat}\n`;
      report += `   ✅ Postate: ${postat}\n\n`;
    } catch(e) {
      report += `🚀 Social: Nu s-a putut accesa\n\n`;
    }
    
    report += '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n';
    report += '🏠 Portal: https://cssibv.github.io/cssi-website/admin.html\n';
    report += 'Generat automat de Portal CSSI v2.0\n';
    
    MailApp.sendEmail({
      to: CONFIG.EMAIL_ADMIN,
      subject: `📊 Raport Zilnic CSSI · ${dateStr}`,
      body: report
    });
    
    Logger.log('✅ Raport zilnic trimis');
    
  } catch (error) {
    Logger.log('❌ Eroare raport zilnic: ' + error.toString());
  }
}


// ============================================================
// 3. API ENDPOINT PENTRU DASHBOARD KPI (Web App)
// ============================================================

/**
 * Returnează JSON cu KPI-uri pentru dashboard.
 * Deploy: Publish → Deploy as Web App → Anyone (even anonymous)
 * URL-ul generat se pune în admin.html
 */
function doGet(e) {
  try {
    const kpi = {
      leads: 0,
      lucrari: 0,
      materiale: 0,
      postari: 0,
      timestamp: new Date().toISOString()
    };
    
    // CRM leads luna asta
    try {
      const crmSheet = SpreadsheetApp.openById(CONFIG.SHEET_CRM).getActiveSheet();
      const crmData = crmSheet.getDataRange().getValues();
      const thisMonth = new Date().getMonth();
      crmData.forEach((row, i) => {
        if (i === 0) return;
        const d = new Date(row[0]);
        if (d.getMonth() === thisMonth) kpi.leads++;
      });
    } catch(e) {}
    
    // Lucrări active (din checklist, ultimele 30 zile)
    try {
      const checkSheet = SpreadsheetApp.openById(CONFIG.SHEET_CHECKLIST).getActiveSheet();
      const checkData = checkSheet.getDataRange().getValues();
      const monthAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000);
      checkData.forEach((row, i) => {
        if (i === 0) return;
        const d = new Date(row[0]);
        if (d >= monthAgo) kpi.lucrari++;
      });
    } catch(e) {}
    
    // Materiale în așteptare
    try {
      const matSheet = SpreadsheetApp.openById(CONFIG.SHEET_MATERIALE).getActiveSheet();
      const matData = matSheet.getDataRange().getValues();
      matData.forEach((row, i) => {
        if (i === 0) return;
        const rowStr = row.join(' ').toLowerCase();
        if (!rowStr.includes('livrat') && !rowStr.includes('completat')) kpi.materiale++;
      });
    } catch(e) {}
    
    // Postări programate
    try {
      const socialSheet = SpreadsheetApp.openById(CONFIG.SHEET_SOCIAL).getActiveSheet();
      const socialData = socialSheet.getDataRange().getValues();
      socialData.forEach((row, i) => {
        if (i === 0) return;
        const rowStr = row.join(' ').toLowerCase();
        if (rowStr.includes('planificat')) kpi.postari++;
      });
    } catch(e) {}
    
    return ContentService
      .createTextOutput(JSON.stringify(kpi))
      .setMimeType(ContentService.MimeType.JSON);
      
  } catch (error) {
    return ContentService
      .createTextOutput(JSON.stringify({error: error.toString()}))
      .setMimeType(ContentService.MimeType.JSON);
  }
}


// ============================================================
// 4. WEBHOOK PENTRU LEAD-URI DE PE SITE (doPost)
// ============================================================

/**
 * Primește lead-uri de pe contact.html via POST request.
 * Adaugă automat în Sheet-ul CRM.
 * 
 * Deploy: Publish → Deploy as Web App → Anyone (even anonymous)
 * Pune URL-ul generat în contact.html (înlocuiește Railway API)
 */
function doPost(e) {
  try {
    const data = JSON.parse(e.postData.contents);
    
    const crmSheet = SpreadsheetApp.openById(CONFIG.SHEET_CRM).getActiveSheet();
    
    // Adaugă rând nou în CRM
    crmSheet.appendRow([
      new Date(),                    // Timestamp
      data.name || '',               // Nume
      data.phone || '',              // Telefon
      data.email || '',              // Email
      data.service || '',            // Serviciu
      data.location || 'Brașov',     // Locație
      '🌐 Website',                  // Sursă
      '🆕 Nou',                      // Status
      '',                            // Valoare estimată
      '',                            // Responsabil
      data.message || ''             // Observații
    ]);
    
    // Notificare email
    MailApp.sendEmail({
      to: CONFIG.EMAIL_ADMIN,
      subject: '🌐 Lead NOU de pe site · CSSI',
      body: `Un vizitator a completat formularul de contact pe site!\n\n` +
            `👤 Nume: ${data.name || 'N/A'}\n` +
            `📞 Telefon: ${data.phone || 'N/A'}\n` +
            `📧 Email: ${data.email || 'N/A'}\n` +
            `🔧 Serviciu: ${data.service || 'N/A'}\n` +
            `📍 Locație: ${data.location || 'N/A'}\n` +
            `💬 Mesaj: ${data.message || 'N/A'}\n\n` +
            `⏰ Contactează în maxim 2 ore!\n\n` +
            `🔗 CRM: https://docs.google.com/spreadsheets/d/${CONFIG.SHEET_CRM}/edit\n` +
            `🏠 Portal: https://cssibv.github.io/cssi-website/admin.html`
    });
    
    return ContentService
      .createTextOutput(JSON.stringify({status: 'ok', message: 'Lead adăugat în CRM'}))
      .setMimeType(ContentService.MimeType.JSON);
      
  } catch (error) {
    return ContentService
      .createTextOutput(JSON.stringify({status: 'error', message: error.toString()}))
      .setMimeType(ContentService.MimeType.JSON);
  }
}


// ============================================================
// 5. INSTALARE TRIGGER-URI AUTOMATE
// ============================================================

/**
 * Rulează o singură dată pentru a configura:
 * - Notificări la completare formulare
 * - Raport zilnic la 08:00
 */
function installTriggers() {
  // Șterge trigger-urile existente
  const triggers = ScriptApp.getProjectTriggers();
  triggers.forEach(t => ScriptApp.deleteTrigger(t));
  
  // 1. Trigger la completare formular (pe Sheet-ul curent)
  ScriptApp.newTrigger('onFormSubmit')
    .forSpreadsheet(SpreadsheetApp.getActiveSpreadsheet())
    .onFormSubmit()
    .create();
  
  // 2. Raport zilnic la 08:00 (luni-vineri)
  ScriptApp.newTrigger('dailyReport')
    .timeBased()
    .atHour(8)
    .everyDays(1)
    .inTimezone('Europe/Bucharest')
    .create();
  
  Logger.log('✅ Trigger-uri instalate cu succes!');
  Logger.log('📧 Notificări active pe: ' + CONFIG.EMAIL_ADMIN);
  Logger.log('📊 Raport zilnic: Luni-Vineri la 08:00');
}


// ============================================================
// 6. FUNCȚII UTILITARE
// ============================================================

/**
 * Testează notificarea email
 */
function testEmailNotification() {
  MailApp.sendEmail({
    to: CONFIG.EMAIL_ADMIN,
    subject: '🧪 Test Notificare · CSSI Portal',
    body: 'Dacă primești acest email, notificările funcționează corect!\n\nPortal CSSI v2.0'
  });
  Logger.log('✅ Email test trimis la: ' + CONFIG.EMAIL_ADMIN);
}

/**
 * Testează raportul zilnic
 */
function testDailyReport() {
  dailyReport();
  Logger.log('✅ Raport zilnic de test trimis');
}

/**
 * Verifică accesul la toate Sheet-urile
 */
function checkAccess() {
  const sheets = [
    {name: 'CRM', id: CONFIG.SHEET_CRM},
    {name: 'Social', id: CONFIG.SHEET_SOCIAL},
    {name: 'Checklist', id: CONFIG.SHEET_CHECKLIST},
    {name: 'Materiale', id: CONFIG.SHEET_MATERIALE},
    {name: 'Calculator', id: CONFIG.SHEET_CALCULATOR}
  ];
  
  sheets.forEach(s => {
    try {
      const ss = SpreadsheetApp.openById(s.id);
      const rows = ss.getActiveSheet().getLastRow();
      Logger.log(`✅ ${s.name}: OK (${rows} rânduri)`);
    } catch(e) {
      Logger.log(`❌ ${s.name}: ${e.toString()}`);
    }
  });
}
