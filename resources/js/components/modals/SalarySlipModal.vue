<template>
  <div v-if="isVisible" class="modal-overlay">
    <div class="modal-content">
      <div class="logo-heading">
        <img src="img/CWlogo.jpeg" alt="Contriwiz Logo" class="logo" />
        <h2>Contriwiz</h2>
      </div>

      <h3 class="modal-title">Salary Slip for the Month of December 2024</h3>

      <div class="modal-details">
        <p><strong>Name:</strong> {{ selectedSalarySlip.employee_name }}</p>
        <p><strong>Total Salary:</strong> Rs. <span class="font-bold">{{ selectedSalarySlip.total_salary }}</span></p>
        <p><strong>Total Leaves:</strong> {{ selectedSalarySlip.total_leaves }} days</p>
        <p><strong>Extra Working Days:</strong> {{ selectedSalarySlip.extra_working_days }}</p>
        <p><strong>Deduction %:</strong> {{ (selectedSalarySlip.total_deductions / selectedSalarySlip.total_salary * 100).toFixed(2) }}%</p>
        <p><strong>Earned Leave:</strong> {{ selectedSalarySlip.earned_leave }}</p>
        <p><strong>Leave without Pay:</strong> {{ selectedSalarySlip.leaves_without_pay }}</p>
      </div>

      <!-- Tables Row -->
      <div class="table-row">
        <div class="table-container">
          <div class="table-box">
            <h4>Pay & Allowances</h4>
            <table class="styled-table">
              <thead>
                <tr>
                  <th>Description</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>Basic</td><td>{{ selectedSalarySlip.basic_salary }}</td></tr>
                <tr><td>House Rent</td><td>{{ selectedSalarySlip.house_rent }}</td></tr>
                <tr><td>Conveyance</td><td>{{ selectedSalarySlip.conveyance }}</td></tr>
                <tr><td>Telephone</td><td>{{ selectedSalarySlip.telephone }}</td></tr>
                <tr><td>Medical</td><td>{{ selectedSalarySlip.medical }}</td></tr>
                <tr><td>Other</td><td>{{ selectedSalarySlip.other }}</td></tr>
                <tr><td class="gross-total">Gross Total: </td><td class="font-bold">{{ selectedSalarySlip.total_salary }}</td></tr>
              </tbody>
            </table>
          </div>

          <div class="table-box">
            <h4>Deductions & Incentives</h4>
            <div class="dual-tables-wrapper">
              <table class="styled-table">
                <thead>
                  <tr>
                    <th>Deductions</th>
                    <th>Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td>Tax</td><td>{{ selectedSalarySlip.tax }}</td></tr>
                  <tr><td>Leaves</td><td>{{ selectedSalarySlip.unpaid_leaves_deduction }}</td></tr>
                  <tr><td v-tooltip="'Provident Fund'">PF</td><td>{{ selectedSalarySlip.provident_fund }}</td></tr>
                  <tr><td v-tooltip="'Employee State Insurance'">ESI</td><td>{{ selectedSalarySlip.employee_state_insurance }}</td></tr>
                  <tr><td class="font-bold">Total DED</td><td class="font-bold">{{ selectedSalarySlip.total_deductions }}</td></tr>
                </tbody>
              </table>

              <table class="styled-table">
                <thead>
                  <tr>
                    <th>Incentives</th>
                    <th>Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td>Extra Working</td><td>{{ selectedSalarySlip.extra_working_incentive }}</td></tr>
                  <tr><td>Gratutiy</td><td>{{ selectedSalarySlip.gratuity }}</td></tr>
                  <tr><td>Bonus</td><td>{{ selectedSalarySlip.bonus }}</td></tr>
                  <tr><td class="font-bold">Total</td><td class="font-bold">{{ selectedSalarySlip.total_incentives }}</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <h3 class="salary-credited">
        Salary Credited To Your Account# :: Rs. <span class="font-bold">{{ selectedSalarySlip.net_salary_credited }}</span>
      </h3>

      <div class="button-container no-print">
        <button @click="printSlip" class="print-button">Print</button>
        <button @click="downloadPDF" class="download-button">Download PDF</button>
        <button @click="$emit('close')" class="close-button">Close</button>
      </div>
    </div>
  </div>
</template>
  
  <script>
  import jsPDF from "jspdf";
  import html2canvas from "html2canvas";
  
  export default {
    name: "SalarySlipModal",
    props: {
      isVisible: Boolean,
      selectedSalarySlip: Object,
    },
    methods: {
      printSlip() {
        // Create an iframe to print the content without showing a new page
        const iframe = document.createElement("iframe");
        iframe.style.position = "absolute";
        iframe.style.width = "0px";
        iframe.style.height = "0px";
        iframe.style.border = "none";
        document.body.appendChild(iframe);
  
        const doc = iframe.contentWindow.document;
        const modalContent = this.$el.querySelector(".modal-content");
  
        // Copy the modal content and page styles into the iframe
        doc.open();
        doc.write("<html><head>");
        
        // Copying all styles from the page
        const styles = Array.from(document.styleSheets)
          .map(sheet => {
            if (sheet.href) {
              return `<link rel="stylesheet" type="text/css" href="${sheet.href}">`;
            } else {
              return Array.from(sheet.cssRules)
                .map(rule => `<style>${rule.cssText}</style>`)
                .join("");
            }
          })
          .join("");
        
        doc.write(styles);  // Write the styles to iframe
        
        // Write modal content
        doc.write("</head><body>");
        doc.write(modalContent.innerHTML);
        doc.write("</body></html>");
        doc.close();
  
        // Print the iframe content
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
  
        // Remove the iframe after printing
        setTimeout(() => {
          document.body.removeChild(iframe);
        }, 1000);
      },
      async downloadPDF() {
        const modalContent = this.$el.querySelector(".modal-content");
  
        // Hide buttons temporarily
        const buttons = modalContent.querySelectorAll("button");
        buttons.forEach((button) => (button.style.display = "none"));
  
        // Capture content as a canvas
        await html2canvas(modalContent).then((canvas) => {
          const imgData = canvas.toDataURL("image/png");
          const pdf = new jsPDF("p", "mm", "a4");
          const pdfWidth = pdf.internal.pageSize.getWidth();
          const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
  
          // Add the image to the PDF
          pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
          pdf.save("Salary_Slip_December_2024.pdf");
        });
  
        // Restore button visibility
        buttons.forEach((button) => (button.style.display = ""));
      },
    },
  };
  </script>
 <style scoped>
@media print {
  .no-print {
    display: none !important;
  }

  .modal-content {
    margin: 0;
    padding: 20px;
    border: none;
    box-shadow: none;
  }

  /* You can add additional print-specific styles if needed */
  table {
    width: 100%;
    border-collapse: collapse;
  }

  th, td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ddd;
  }

  .highlight {
    font-weight: bold;
    color: #000;
  }
}
 .v-tooltip {
  background-color: #333; /* Dark background for better visibility */
  color: #fff;
  font-size: 14px;
  padding: 8px 12px;
  border-radius: 6px;
  text-align: center;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.15);
  transition: opacity 0.3s;
}

.v-tooltip::before {
  content: "";
  width: 0;
  height: 0;
  position: absolute;
  border-style: solid;
  border-width: 5px;
  border-color: transparent transparent #333 transparent;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
}

 .font-bold{
    font-weight: bold;
 }
 .table-container {
  display: flex;
  gap: 1rem;
  justify-content: space-between;
  align-items: stretch; /* Makes both tables same height */
}
 .modal-overlay {
   position: fixed;
   inset: 0;
   background: rgba(75, 85, 99, 0.5);
   overflow-y: auto;
   height: 100%;
   width: 100%;
 }
 .modal-content {
   position: relative;
   top: 3rem;
   margin: auto;
   padding: 2rem;
   border: 1px solid #ddd;
   width: 80%;
   max-width: 70rem;
   background: white;
   box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
   border-radius: 0.75rem;
 }
 .logo-heading {
   text-align: center;
   margin-bottom: 1rem;
 }
 .logo {
   margin: auto;
   width: 4rem;
   height: 4rem;
   margin-bottom: 0.5rem;
 }
 .modal-title {
   text-align: center;
   font-size: 1rem;
   margin-bottom: 1rem;
 }
 .modal-details {
   display: grid;
   grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
   gap: 1rem;
   margin-bottom: 1rem;
   font-size: 0.9rem;
 }
 .table-row {
   display: flex;
   justify-content: space-between;
   gap: 1rem;
   margin-top: 1.5rem;
 }
 .table-box {
  flex: 1; /* Ensures equal width */
  display: flex;
  flex-direction: column;
  padding: 1rem;
  border-radius: 0.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  background: white;
}
.styled-table {
  width: 100%;
  height: 100%; /* Ensures tables stretch equally */
  table-layout: fixed; /* Prevents uneven column sizing */
  border-collapse: collapse;
}

.styled-table th, .styled-table td {
  border: 1px solid #333;
  padding: 0.75rem;
  text-align: left;
}

.dual-tables-wrapper {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  height: 100%;
}

.dual-tables-wrapper table {
  flex: 1;
}
 .button-container {
   display: flex;
   justify-content: flex-end;
   gap: 0.5rem;
   margin-top: 1.5rem;
 }
 .print-button,
 .download-button,
 .close-button {
   padding: 0.5rem 1rem;
   border-radius: 0.25rem;
   border: none;
   cursor: pointer;
   font-size: 0.9rem;
 }
 .print-button {
   background: #3b82f6;
   color: white;
 }
 .print-button:hover {
   background: #2563eb;
 }
 .download-button {
   background: #10b981;
   color: white;
 }
 .download-button:hover {
   background: #059669;
 }
 .close-button {
   background: #ef4444;
   color: white;
 }
 .close-button:hover {
   background: #dc2626;
 }
 .gross-total,
 .salary-credited {
   margin-top: 1rem;
   font-weight: bold;
   font-size: 1rem;
 }

 </style>
  