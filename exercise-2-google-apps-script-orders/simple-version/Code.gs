/**
 * Simple Version - Google Apps Script for Order Processing
 * 
 * This is a basic implementation that moves "Pending" orders from "Orders" sheet
 * to "To Process" sheet. Suitable for learning and simple workflows.
 * 
 * Features:
 * - Basic error handling
 * - Simple sheet references
 * - Manual header matching
 * - Straightforward implementation
 */

function movePendingOrders() {
  try {
    // Get spreadsheet and sheets
    const ss = SpreadsheetApp.getActiveSpreadsheet();
    const ordersSheet = ss.getSheetByName("Orders");
    const toProcessSheet = ss.getSheetByName("To Process");
    
    // Basic validation
    if (!ordersSheet || !toProcessSheet) {
      Logger.log("Error: Required sheets not found");
      return;
    }
    
    // Get all data from Orders sheet
    const data = ordersSheet.getDataRange().getValues();
    const headers = data[0];
    
    // Find Status column index
    const statusIndex = headers.indexOf("Status");
    if (statusIndex === -1) {
      Logger.log("Error: Status column not found");
      return;
    }
    
    // Filter pending orders
    const pendingRows = [];
    for (let i = 1; i < data.length; i++) {
      if (data[i][statusIndex].toString().toLowerCase() === "pending") {
        pendingRows.push(data[i]);
      }
    }
    
    // Move pending orders to To Process sheet
    if (pendingRows.length > 0) {
      toProcessSheet.getRange(toProcessSheet.getLastRow() + 1, 1, pendingRows.length, pendingRows[0].length)
        .setValues(pendingRows);
      
      // Remove moved rows from Orders sheet (bottom-up to avoid index issues)
      for (let i = data.length - 1; i > 0; i--) {
        if (data[i][statusIndex].toString().toLowerCase() === "pending") {
          ordersSheet.deleteRow(i + 1);
        }
      }
      
      Logger.log("Successfully moved " + pendingRows.length + " pending orders");
    } else {
      Logger.log("No pending orders found");
    }
    
  } catch (error) {
    Logger.log("Error: " + error.message);
  }
}
