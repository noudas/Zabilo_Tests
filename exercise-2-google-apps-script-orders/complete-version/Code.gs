/**
 * Complete Version - Google Apps Script for Order Processing
 * 
 * This is a production-ready implementation with comprehensive error handling,
 * logging, validation, and extensible features. Suitable for enterprise use.
 * 
 * Features:
 * - Comprehensive error handling and validation
 * - Detailed logging and optional email notifications
 * - Dynamic column detection and validation
 * - Performance optimizations
 * - Extensible architecture
 * - Production-ready code structure
 */

// Configuration object for easy customization
const CONFIG = {
  SHEET_NAMES: {
    ORDERS: "Orders",
    TO_PROCESS: "To Process",
    LOGS: "Script Logs" // Optional logging sheet
  },
  COLUMN_NAMES: {
    STATUS: "Status",
    TIMESTAMP: "Processed At" // For tracking when orders were moved
  },
  STATUS_VALUES: {
    PENDING: "pending"
  },
  NOTIFICATIONS: {
    ENABLED: true,
    ADMIN_EMAIL: "admin@example.com",
    SUBJECT: "Order Processing Script - Status Report"
  },
  LOGGING: {
    ENABLED: true,
    LOG_TO_SHEET: true
  }
};

/**
 * Main function to move pending orders from Orders sheet to To Process sheet
 * Includes comprehensive error handling, logging, and validation
 */
function movePendingOrders() {
  const startTime = new Date();
  let processedCount = 0;
  let errorCount = 0;
  let logMessages = [];
  
  try {
    logMessages.push("Starting order processing at " + startTime.toISOString());
    
    // 1. Initialize and validate spreadsheet
    const spreadsheet = initializeSpreadsheet();
    if (!spreadsheet) {
      throw new Error("Failed to initialize spreadsheet");
    }
    
    // 2. Validate sheets and get references
    const { ordersSheet, toProcessSheet, logsSheet } = validateAndGetSheets(spreadsheet);
    
    // 3. Validate data structure
    const { data, headers, statusIndex } = validateDataStructure(ordersSheet);
    
    // 4. Process pending orders
    const result = processPendingOrders(data, headers, statusIndex, ordersSheet, toProcessSheet);
    processedCount = result.processedCount;
    
    // 5. Log results
    logResults(logsSheet, processedCount, startTime, logMessages);
    
    // 6. Send notification if enabled
    if (CONFIG.NOTIFICATIONS.ENABLED) {
      sendNotification(processedCount, errorCount, startTime);
    }
    
    logMessages.push("Order processing completed successfully. Processed: " + processedCount);
    
  } catch (error) {
    errorCount++;
    const errorMessage = "Critical error in movePendingOrders: " + error.message;
    Logger.log(errorMessage);
    logMessages.push(errorMessage);
    
    // Send error notification
    if (CONFIG.NOTIFICATIONS.ENABLED) {
      sendErrorNotification(error.message, startTime);
    }
  }
}

/**
 * Initialize and validate the spreadsheet
 */
function initializeSpreadsheet() {
  try {
    const ss = SpreadsheetApp.getActiveSpreadsheet();
    if (!ss) {
      throw new Error("No active spreadsheet found");
    }
    return ss;
  } catch (error) {
    Logger.log("Error initializing spreadsheet: " + error.message);
    return null;
  }
}

/**
 * Validate sheets and get references
 */
function validateAndGetSheets(spreadsheet) {
  const ordersSheet = spreadsheet.getSheetByName(CONFIG.SHEET_NAMES.ORDERS);
  const toProcessSheet = spreadsheet.getSheetByName(CONFIG.SHEET_NAMES.TO_PROCESS);
  const logsSheet = CONFIG.LOGGING.LOG_TO_SHEET ? 
    spreadsheet.getSheetByName(CONFIG.SHEET_NAMES.LOGS) : null;
  
  // Validate required sheets
  if (!ordersSheet) {
    throw new Error("Orders sheet not found: " + CONFIG.SHEET_NAMES.ORDERS);
  }
  
  if (!toProcessSheet) {
    throw new Error("To Process sheet not found: " + CONFIG.SHEET_NAMES.TO_PROCESS);
  }
  
  return { ordersSheet, toProcessSheet, logsSheet };
}

/**
 * Validate data structure and get column indices
 */
function validateDataStructure(ordersSheet) {
  const data = ordersSheet.getDataRange().getValues();
  
  if (data.length === 0) {
    throw new Error("Orders sheet is empty");
  }
  
  const headers = data[0];
  const statusIndex = headers.indexOf(CONFIG.COLUMN_NAMES.STATUS);
  
  if (statusIndex === -1) {
    throw new Error("Status column not found. Available columns: " + headers.join(", "));
  }
  
  return { data, headers, statusIndex };
}

/**
 * Process pending orders with comprehensive validation
 */
function processPendingOrders(data, headers, statusIndex, ordersSheet, toProcessSheet) {
  const pendingRows = [];
  const processedRows = [];
  
  // Filter pending orders with validation
  for (let i = 1; i < data.length; i++) {
    const row = data[i];
    const status = row[statusIndex];
    
    if (status && status.toString().toLowerCase() === CONFIG.STATUS_VALUES.PENDING) {
      // Add timestamp to track when order was processed
      const processedRow = [...row];
      if (headers.includes(CONFIG.COLUMN_NAMES.TIMESTAMP)) {
        const timestampIndex = headers.indexOf(CONFIG.COLUMN_NAMES.TIMESTAMP);
        processedRow[timestampIndex] = new Date();
      }
      
      pendingRows.push(processedRow);
      processedRows.push(i + 1); // Store row number for deletion
    }
  }
  
  // Move pending orders to To Process sheet
  if (pendingRows.length > 0) {
    // Batch write for better performance
    const range = toProcessSheet.getRange(
      toProcessSheet.getLastRow() + 1, 
      1, 
      pendingRows.length, 
      pendingRows[0].length
    );
    range.setValues(pendingRows);
    
    // Remove processed rows from Orders sheet (bottom-up to avoid index issues)
    for (let i = processedRows.length - 1; i >= 0; i--) {
      ordersSheet.deleteRow(processedRows[i]);
    }
  }
  
  return { processedCount: pendingRows.length };
}

/**
 * Log results to sheet and console
 */
function logResults(logsSheet, processedCount, startTime, logMessages) {
  const endTime = new Date();
  const duration = endTime.getTime() - startTime.getTime();
  
  const logEntry = {
    timestamp: endTime,
    processedCount: processedCount,
    duration: duration + "ms",
    status: "SUCCESS"
  };
  
  // Log to console
  Logger.log("Processing completed: " + JSON.stringify(logEntry));
  
  // Log to sheet if enabled
  if (logsSheet && CONFIG.LOGGING.LOG_TO_SHEET) {
    try {
      const logRow = [
        logEntry.timestamp,
        logEntry.processedCount,
        logEntry.duration,
        logEntry.status
      ];
      
      logsSheet.getRange(logsSheet.getLastRow() + 1, 1, 1, logRow.length)
        .setValues([logRow]);
    } catch (error) {
      Logger.log("Error writing to log sheet: " + error.message);
    }
  }
}

/**
 * Send success notification
 */
function sendNotification(processedCount, errorCount, startTime) {
  try {
    const endTime = new Date();
    const duration = endTime.getTime() - startTime.getTime();
    
    const subject = CONFIG.NOTIFICATIONS.SUBJECT;
    const body = `
Order Processing Script Report

Processed Orders: ${processedCount}
Errors: ${errorCount}
Start Time: ${startTime.toISOString()}
End Time: ${endTime.toISOString()}
Duration: ${duration}ms
Status: ${errorCount > 0 ? 'COMPLETED WITH ERRORS' : 'SUCCESS'}

This is an automated report from the Google Apps Script order processing system.
    `.trim();
    
    MailApp.sendEmail(CONFIG.NOTIFICATIONS.ADMIN_EMAIL, subject, body);
  } catch (error) {
    Logger.log("Error sending notification: " + error.message);
  }
}

/**
 * Send error notification
 */
function sendErrorNotification(errorMessage, startTime) {
  try {
    const subject = "ERROR: " + CONFIG.NOTIFICATIONS.SUBJECT;
    const body = `
Order Processing Script Error Report

Error: ${errorMessage}
Start Time: ${startTime.toISOString()}
Status: FAILED

Please check the Apps Script logs for more details.
    `.trim();
    
    MailApp.sendEmail(CONFIG.NOTIFICATIONS.ADMIN_EMAIL, subject, body);
  } catch (error) {
    Logger.log("Error sending error notification: " + error.message);
  }
}

/**
 * Test function to validate the setup
 */
function testSetup() {
  try {
    Logger.log("Testing setup...");
    
    const spreadsheet = initializeSpreadsheet();
    if (!spreadsheet) {
      Logger.log("❌ Spreadsheet initialization failed");
      return false;
    }
    
    const { ordersSheet, toProcessSheet } = validateAndGetSheets(spreadsheet);
    if (!ordersSheet || !toProcessSheet) {
      Logger.log("❌ Sheet validation failed");
      return false;
    }
    
    const { data, headers, statusIndex } = validateDataStructure(ordersSheet);
    if (statusIndex === -1) {
      Logger.log("❌ Status column not found");
      return false;
    }
    
    Logger.log("✅ Setup validation passed");
    Logger.log("📊 Orders sheet has " + (data.length - 1) + " data rows");
    Logger.log("📋 Available columns: " + headers.join(", "));
    
    return true;
  } catch (error) {
    Logger.log("❌ Setup test failed: " + error.message);
    return false;
  }
}

/**
 * Create logs sheet if it doesn't exist
 */
function createLogsSheet() {
  try {
    const ss = SpreadsheetApp.getActiveSpreadsheet();
    let logsSheet = ss.getSheetByName(CONFIG.SHEET_NAMES.LOGS);
    
    if (!logsSheet) {
      logsSheet = ss.insertSheet(CONFIG.SHEET_NAMES.LOGS);
      logsSheet.getRange(1, 1, 1, 4).setValues([["Timestamp", "Processed Count", "Duration", "Status"]]);
      logsSheet.getRange(1, 1, 1, 4).setFontWeight("bold");
      Logger.log("Created logs sheet: " + CONFIG.SHEET_NAMES.LOGS);
    }
    
    return logsSheet;
  } catch (error) {
    Logger.log("Error creating logs sheet: " + error.message);
    return null;
  }
}
