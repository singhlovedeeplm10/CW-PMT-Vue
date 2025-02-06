<template>
  <div v-if="isVisible" class="modal-overlay">
    <div class="modal-content">
      <div class="logo-heading">
        <img src="img/CWlogo.jpeg" alt="Contriwiz Logo" class="logo" />
        <h2>Contriwiz</h2>
      </div>

      <h3 class="modal-title">Salary Slip for the Month of {{ selectedSalarySlip.month_year }}</h3>

      <div class="modal-details">
        <p><strong>Name:</strong> 
          <span v-if="!isEditing">{{ selectedSalarySlip.employee_name }}</span>
          <input v-if="isEditing" v-model="selectedSalarySlip.employee_name" type="text" />
        </p>
        <p><strong>Total Salary:</strong> Rs. 
          <span v-if="!isEditing" class="font-bold">{{ selectedSalarySlip.total_salary }}</span>
          <input v-if="isEditing" v-model="selectedSalarySlip.total_salary" type="number" />
        </p>
        <p><strong>Total Leaves:</strong> 
          <span v-if="!isEditing">{{ selectedSalarySlip.total_leaves }} days</span>
          <input v-if="isEditing" v-model="selectedSalarySlip.total_leaves" type="number" />
        </p>
        <p><strong>Extra Working Days:</strong> 
          <span v-if="!isEditing">{{ selectedSalarySlip.extra_working_days }}</span>
          <input v-if="isEditing" v-model="selectedSalarySlip.extra_working_days" type="number" />
        </p>
        <p><strong>Deduction %:</strong> 
          <span v-if="!isEditing">{{ (selectedSalarySlip.total_deductions / selectedSalarySlip.total_salary * 100).toFixed(2) }}%</span>
          <input v-if="isEditing" v-model="selectedSalarySlip.total_deductions" type="number" />
        </p>
        <p><strong>Earned Leave:</strong> 
          <span v-if="!isEditing">{{ selectedSalarySlip.earned_leave }}</span>
          <input v-if="isEditing" v-model="selectedSalarySlip.earned_leave" type="number" />
        </p>
        <p><strong>Leave without Pay:</strong> 
          <span v-if="!isEditing">{{ selectedSalarySlip.leaves_without_pay }}</span>
          <input v-if="isEditing" v-model="selectedSalarySlip.leaves_without_pay" type="number" />
        </p>
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
                <tr><td>Basic</td><td>
                  <span v-if="!isEditing">{{ selectedSalarySlip.basic_salary }}</span>
                  <input v-if="isEditing" v-model="selectedSalarySlip.basic_salary" type="number" />
                </td></tr>
                <tr><td>House Rent</td><td>
                  <span v-if="!isEditing">{{ selectedSalarySlip.house_rent }}</span>
                  <input v-if="isEditing" v-model="selectedSalarySlip.house_rent" type="number" />
                </td></tr>
                <tr><td>Conveyance</td><td>
                  <span v-if="!isEditing">{{ selectedSalarySlip.conveyance }}</span>
                  <input v-if="isEditing" v-model="selectedSalarySlip.conveyance" type="number" />
                </td></tr>
                <tr><td>Telephone</td><td>
                  <span v-if="!isEditing">{{ selectedSalarySlip.telephone }}</span>
                  <input v-if="isEditing" v-model="selectedSalarySlip.telephone" type="number" />
                </td></tr>
                <tr><td>Medical</td><td>
                  <span v-if="!isEditing">{{ selectedSalarySlip.medical }}</span>
                  <input v-if="isEditing" v-model="selectedSalarySlip.medical" type="number" />
                </td></tr>
                <tr><td>Other</td><td>
                  <span v-if="!isEditing">{{ selectedSalarySlip.other }}</span>
                  <input v-if="isEditing" v-model="selectedSalarySlip.other" type="number" />
                </td></tr>
                <tr><td class="gross-total">Gross Total: </td><td class="font-bold">
                  <span v-if="!isEditing">{{ selectedSalarySlip.total_salary }}</span>
                  <input v-if="isEditing" v-model="selectedSalarySlip.total_salary" type="number" />
                </td></tr>
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
        <tr>
          <td>Tax</td>
          <td>
            <span v-if="!isEditing">{{ selectedSalarySlip.tax }}</span>
            <input v-if="isEditing" v-model="selectedSalarySlip.tax" type="number" class="input-field" />
          </td>
        </tr>
        <tr>
          <td>Leaves</td>
          <td>
            <span v-if="!isEditing">{{ selectedSalarySlip.unpaid_leaves_deduction }}</span>
            <input v-if="isEditing" v-model="selectedSalarySlip.unpaid_leaves_deduction" type="number" class="input-field" />
          </td>
        </tr>
        <tr>
          <td v-tooltip="'Provident Fund'">PF</td>
          <td>
            <span v-if="!isEditing">{{ selectedSalarySlip.provident_fund }}</span>
            <input v-if="isEditing" v-model="selectedSalarySlip.provident_fund" type="number" class="input-field" />
          </td>
        </tr>
        <tr>
          <td v-tooltip="'Employee State Insurance'">ESI</td>
          <td>
            <span v-if="!isEditing">{{ selectedSalarySlip.employee_state_insurance }}</span>
            <input v-if="isEditing" v-model="selectedSalarySlip.employee_state_insurance" type="number" class="input-field" />
          </td>
        </tr>
        <tr>
          <td class="font-bold">Total DED</td>
          <td class="font-bold">
            <span v-if="!isEditing">{{ selectedSalarySlip.total_deductions }}</span>
                  <input v-if="isEditing" v-model="selectedSalarySlip.total_deductions" type="number" class="input-field" />
          </td>
        </tr>
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
        <tr>
          <td>Extra Working</td>
          <td>
            <span v-if="!isEditing">{{ selectedSalarySlip.extra_working_incentive }}</span>
            <input v-if="isEditing" v-model="selectedSalarySlip.extra_working_incentive" type="number" class="input-field" />
          </td>
        </tr>
        <tr>
          <td>Gratuity</td>
          <td>
            <span v-if="!isEditing">{{ selectedSalarySlip.gratuity }}</span>
            <input v-if="isEditing" v-model="selectedSalarySlip.gratuity" type="number" class="input-field" />
          </td>
        </tr>
        <tr>
          <td>Bonus</td>
          <td>
            <span v-if="!isEditing">{{ selectedSalarySlip.bonus }}</span>
            <input v-if="isEditing" v-model="selectedSalarySlip.bonus" type="number" class="input-field" />
          </td>
        </tr>
        <tr>
          <td class="font-bold">Total</td>
          <td class="font-bold">
            <span v-if="!isEditing">{{ selectedSalarySlip.total_incentives }}</span>
                  <input v-if="isEditing" v-model="selectedSalarySlip.total_incentives" type="number" class="input-field" />
          </td>
        </tr>
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
        <button v-if="userRole === 'Admin'" @click="toggleEdit" class="edit-button">
          {{ isEditing ? 'Save' : 'Edit' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import jsPDF from "jspdf";
import html2canvas from "html2canvas";
import { toast } from "vue3-toastify";  // Import toast
import "vue3-toastify/dist/index.css";

export default {
  name: "SalarySlipModal",
  props: {
    isVisible: Boolean,
    selectedSalarySlip: Object,
  },
  data() {
    return {
      userRole: null,
      isEditing: false,
    };
  },
  methods: {
    async fetchUserRole() {
      try {
        const response = await axios.get("/api/user-role", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        this.userRole = response.data.role;
      } catch (error) {
        console.error("Error fetching user role:", error);
      }
    },
    async toggleEdit() {
      if (this.isEditing) {
        // If the user is saving, send the updated data to the backend
        try {
          const response = await axios.put(`/api/update-salary-slip/${this.selectedSalarySlip.id}`, this.selectedSalarySlip);
          toast.success('Salary Slip updated successfully');
          this.$emit("salaryupdated");
        } catch (error) {
          console.error(error);
          toast.error('Failed to update Salary Slip');
        }
      }
      this.isEditing = !this.isEditing;
    },
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

      // Show toast message after printing
      toast.success("Salary Slip Printed Successfully");
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

      // Show toast message after PDF download
      toast.success("Salary Slip Downloaded as PDF");
    },
  },
  mounted() {
    this.fetchUserRole();
  },
};
</script>


 <style scoped>
 /* Create a new class for input fields */
.input-field {
  width: 96px; /* Set a fixed width for the input fields */
  padding: 5px 10px; /* Add padding inside the input fields */
  font-size: 14px; /* Adjust font size for readability */
  box-sizing: border-box; /* Ensure padding and border are included in the width */
}

/* Optionally, you can set a max-width for the inputs */
.input-field {
  max-width: 150px; /* Max width for inputs */
}

/* Focus effect for inputs */
.input-field:focus {
  border-color: #4CAF50; /* Change border color when focused */
  outline: none; /* Remove default outline on focus */
}

/* Styling for tables */
.styled-table input[type="number"] {
  margin: 0;
  padding-left: 10px;
}

.styled-table td {
  vertical-align: middle;
}

.styled-table td input[type="number"]:focus {
  border-color: #4CAF50;
  outline: none;
}

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
 .close-button,
 .edit-button {
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
 .edit-button {
   background: #09365b;
   color: white;
 }
 .edit-button:hover {
  background: #09365b;
 }
 .gross-total,
 .salary-credited {
   margin-top: 1rem;
   font-weight: bold;
   font-size: 1rem;
 }

 </style>
  