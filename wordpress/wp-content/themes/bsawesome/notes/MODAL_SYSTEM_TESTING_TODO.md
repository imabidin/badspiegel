# 🧪 MODAL SYSTEM TESTING TODO
## Nach Zusammenführung von modal.js + datalink.js

**Datum**: 28. August 2025  
**Version**: Modal System v3.0  
**Status**: ⚠️ READY FOR TESTING

---

## 📋 **TESTING CHECKLIST**

### ✅ **1. GRUNDLEGENDE MODAL-FUNKTIONALITÄT**
- [ ] **Manual Modal Creation**
  ```javascript
  createModal({
    title: 'Test Modal',
    body: '<p>Test Content</p>',
    footer: [{ text: 'Close', class: 'btn-secondary', dismiss: true }]
  });
  ```
- [x] **Modal öffnet sich korrekt**
- [x] **Modal schließt sich mit Close-Button**
- [x] **Modal schließt sich mit ESC-Taste**
- [x] **Modal schließt sich mit Backdrop-Click**
- [x] **Mehrere Modals können geöffnet werden (verschiedene IDs)**

### ✅ **2. HTML CONTENT LOADING**
- [x] **data-modal-link Attribute funktionieren**
  ```html
  <button data-modal-link="configurator/test"
          data-modal-title="Test Title">Open HTML Modal</button>
  ```
- [ ] **AJAX Request wird korrekt gesendet**
- [x] **Loading Spinner wird angezeigt**
- [x] **Content wird geladen und angezeigt**
- [ ] **Fehlerbehandlung bei 404/403/500**
- [x] **Smooth Content Transition (Fade-out/in)**

### ✅ **3. IMAGE MODAL LOADING (NEU)**
- [x] **data-modal-image Attribute funktionieren**
  ```html
  <button data-modal-image="1207"
          data-modal-title="Test Image">Open Image Modal</button>
  ```
- [ ] **Image AJAX Request wird korrekt gesendet**
- [x] **Image Loading Spinner wird angezeigt**
- [x] **WordPress Shortcode [img] wird verarbeitet**
- [x] **Bild wird responsive in XL-Modal angezeigt**
- [ ] **Fehlerbehandlung bei ungültiger Image-ID**

### ✅ **4. OPTION-OFFDROPS INTEGRATION**
- [x] **Fallback für Optionswerte ohne Beschreibung aber mit Bild**
  - Option hat `sub_image` ✅
  - Option hat KEINE `sub_description_file` ❌
  - Option hat KEINE `sub_description` ❌
  - **Erwartung**: Lupe-Icon (`fa-magnifying-glass`) wird angezeigt
- [x] **Klick auf Lupe-Icon öffnet Image-Modal**
- [x] **Bild wird in korrekter Größe geladen**
- [x] **Modal-Titel ist der Optionswert-Label**

### ✅ **5. PLACEHOLDER-OPTIONEN INTEGRATION**
- [ ] **Fallback für Placeholder ohne Beschreibung aber mit Bild**
  - Placeholder hat `option_placeholder_image` ✅
  - Placeholder hat KEINE `option_placeholder_description_file` ❌
  - Placeholder hat KEINE `option_placeholder_description` ❌
  - **Erwartung**: Lupe-Icon wird angezeigt
- [ ] **Klick auf Lupe-Icon öffnet Image-Modal für Placeholder**

### ✅ **6. CACHING SYSTEM**
- [ ] **Cache ist standardmäßig deaktiviert** (`MODAL_CACHE_ENABLED = false`)
- [ ] **Cache aktivieren und testen**:
  ```javascript
  // In Browser Console:
  MODAL_CACHE_ENABLED = true; // Änderung in modal.js
  ```
- [ ] **Erster Load**: Content wird von Server geladen
- [ ] **Zweiter Load**: Content wird aus Cache geladen (schneller)
- [ ] **Cache TTL**: Content läuft nach 5 Minuten ab

### ✅ **7. PRELOADING SYSTEM**
- [ ] **Preload-Markierung funktioniert**
  ```html
  <button data-modal-link="configurator/test"
          data-modal-preload="true">Preload Modal</button>
  ```
- [ ] **Content wird beim Seitenload vorgeladen**
- [ ] **Preloaded Modals öffnen sich sofort**
- [ ] **Element bekommt `data-modal-preloaded="true"` Attribut**
- [ ] **Element bekommt `modal-preloaded` CSS-Klasse**

### ✅ **8. EVENT HANDLING**
- [ ] **Click Events funktionieren**
- [ ] **Keyboard Events (Enter) funktionieren**
- [ ] **Event Delegation funktioniert** (dynamisch hinzugefügte Buttons)
- [ ] **Keine Memory Leaks** (Event Listeners werden korrekt entfernt)

### ✅ **9. ERROR HANDLING**
- [ ] **AJAX Fehler werden angezeigt**
- [ ] **Nonce-Fehler werden behandelt**
- [ ] **Ungültige Image-IDs werden behandelt**
- [ ] **Missing WordPress Functions werden behandelt**
- [ ] **Bootstrap nicht verfügbar wird behandelt**

### ✅ **10. PERFORMANCE & DEBUGGING**
- [ ] **Debug Mode aktivieren**:
  ```javascript
  // In modal.js ändern:
  const MODAL_DEBUG_ENABLED = true;
  ```
- [ ] **Console Logs erscheinen korrekt**
- [ ] **Debug Utilities verfügbar**:
  ```javascript
  window.modalDebug.cacheStatus();
  window.modalDebug.clearCache();
  ```
- [ ] **Keine JavaScript Errors in Console**
- [ ] **Performance ist akzeptabel** (Loading Times < 2s)

### ✅ **11. MOBILE COMPATIBILITY**
- [ ] **Modals funktionieren auf Mobile**
- [ ] **Touch Events funktionieren**
- [ ] **Modal ist responsive**
- [ ] **Image Modals sind mobile-optimiert**

### ✅ **12. ACCESSIBILITY**
- [ ] **ARIA Labels sind korrekt**
- [ ] **Keyboard Navigation funktioniert**
- [ ] **Screen Reader Compatibility**
- [ ] **Focus Management**

---

## 🚨 **KRITISCHE TESTS**

### **TEST 1: Basis Image Modal**
```html
<!-- Test Button in Configurator -->
<button type="button" 
        data-modal-image="1207" 
        data-modal-title="Test Bild"
        class="btn btn-primary">
    Test Image Modal
</button>
```

### **TEST 2: Option ohne Beschreibung aber mit Bild**
1. Gehe zu einem Configurator mit Bildern
2. Finde Option mit Bild aber ohne Beschreibung
3. Schaue ob Lupe-Icon erscheint
4. Klicke darauf und teste Modal

### **TEST 3: Webpack Build**
```bash
# Terminal ausführen:
npm run build
# oder
npm run dev
```

---

## 🐛 **BEKANNTE POTENZIELLE PROBLEME**

### **P1: Build Process**
- [x] **Webpack Build funktioniert ohne Errors**
- [x] **Neue modal.js wird korrekt kompiliert**
- [x] **Keine Missing Dependencies**

### **P2: Function Conflicts**
- [ ] **Keine doppelten Function Definitions**
- [ ] **window.createModal ist verfügbar**
- [ ] **Alle Event Handlers funktionieren**

### **P3: PHP Integration**
- [ ] **handle_image_modal_request() wird aufgerufen**
- [ ] **WordPress Shortcode [img] funktioniert**
- [ ] **Nonce Verification funktioniert**

---

## 📊 **TESTING PROTOKOLL**

| Test | Status | Notizen | Datum |
|------|--------|---------|-------|
| Manual Modal Creation | ⏳ | | |
| HTML Content Loading | ⏳ | | |
| Image Modal Loading | ⏳ | | |
| Option Fallback | ⏳ | | |
| Placeholder Fallback | ⏳ | | |
| Caching System | ⏳ | | |
| Preloading | ⏳ | | |
| Event Handling | ⏳ | | |
| Error Handling | ⏳ | | |
| Performance | ⏳ | | |
| Mobile | ⏳ | | |
| Accessibility | ⏳ | | |

**Legende:**
- ⏳ Pending
- ✅ Passed  
- ❌ Failed
- ⚠️ Issues Found

---

## 🛠️ **NACH DEM TESTING**

### **Bei Erfolg:**
- [ ] Debug Mode deaktivieren (`MODAL_DEBUG_ENABLED = false`)
- [ ] Cache aktivieren für Production (`MODAL_CACHE_ENABLED = true`)
- [ ] Documentation aktualisieren
- [ ] Performance Monitoring einrichten

### **Bei Problemen:**
- [ ] Debug Logs analysieren
- [ ] Browser Network Tab checken  
- [ ] PHP Error Logs prüfen
- [ ] Rollback zu vorheriger Version wenn nötig

---

## 📞 **SUPPORT**

**Bei Problemen während Testing:**
1. Browser Console öffnen und Errors notieren
2. Network Tab für AJAX Requests prüfen
3. PHP Error Logs checken
4. Debug Mode aktivieren für mehr Details

**Debug Commands:**
```javascript
// Cache Status
window.modalDebug.cacheStatus();

// Clear Cache
window.modalDebug.clearCache();

// Manual Modal Test
createModal({title: 'Test', body: 'Working!'});
```
