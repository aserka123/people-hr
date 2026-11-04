// One config entry per HR module. ModulePage.vue renders the list, search, form and actions from this.
import api from './api';

const STATUS = (...opts) => opts.map((o) => ({ value: o, label: o[0].toUpperCase() + o.slice(1) }));
const dept = { name: 'department_id', label: 'Department', type: 'select', remote: 'departments', remoteLabel: 'name' };
const employee = { name: 'employee_id', label: 'Employee', type: 'select', remote: 'employees', remoteLabel: 'name', required: true, hrOnly: true };
const payDecision = (status) => (r) => api.post(`leave-requests/${r.id}/decide`, { status });

export const modules = [
  { key: 'employees', title: 'Employees', blurb: 'Profiles, roles and pay details.', endpoint: 'employees',
    columns: [{ key: 'employee_no', label: 'No.' }, { key: 'name', label: 'Name' }, { key: 'position', label: 'Position' },
      { key: 'department.name', label: 'Department' }, { key: 'hire_date', label: 'Hired' }, { key: 'status', label: 'Status', badge: true }],
    fields: [{ name: 'employee_no', label: 'Employee no.', required: true }, { name: 'first_name', label: 'First name', required: true },
      { name: 'last_name', label: 'Last name', required: true }, { name: 'email', label: 'Email', type: 'email', required: true },
      { name: 'phone', label: 'Phone' }, { name: 'position', label: 'Position', required: true }, dept,
      { name: 'hire_date', label: 'Hire date', type: 'date', required: true },
      { name: 'basic_salary', label: 'Monthly basic salary', type: 'number', required: true, hrOnly: true },
      { name: 'allowances', label: 'Monthly allowances', type: 'number', hrOnly: true },
      { name: 'status', label: 'Status', type: 'select', options: STATUS('active', 'inactive'), default: 'active' }] },

  { key: 'attendances', title: 'Attendance', blurb: 'Daily time records. Employees clock in and out from the dashboard.', endpoint: 'attendances', selfCreate: false,
    columns: [{ key: 'date', label: 'Date' }, { key: 'employee.name', label: 'Employee' }, { key: 'clock_in', label: 'In', time: true },
      { key: 'clock_out', label: 'Out', time: true }, { key: 'hours', label: 'Hours', num: true }, { key: 'status', label: 'Status', badge: true }],
    fields: [employee, { name: 'date', label: 'Date', type: 'date', required: true }, { name: 'clock_in', label: 'Clock in', type: 'time' },
      { name: 'clock_out', label: 'Clock out', type: 'time' }, { name: 'hours', label: 'Hours worked', type: 'number' },
      { name: 'status', label: 'Status', type: 'select', options: STATUS('present', 'late', 'absent'), default: 'present' }] },

  { key: 'leave-requests', title: 'Leave', blurb: 'Requests, approvals and yearly balances.', endpoint: 'leave-requests', selfCreate: true,
    columns: [{ key: 'employee.name', label: 'Employee' }, { key: 'leave_type.name', label: 'Type' }, { key: 'start_date', label: 'From' },
      { key: 'end_date', label: 'To' }, { key: 'days', label: 'Days', num: true }, { key: 'status', label: 'Status', badge: true }],
    fields: [employee, { name: 'leave_type_id', label: 'Leave type', type: 'select', remote: 'leave-types', remoteLabel: 'name', required: true },
      { name: 'start_date', label: 'Start date', type: 'date', required: true }, { name: 'end_date', label: 'End date', type: 'date', required: true },
      { name: 'reason', label: 'Reason', type: 'textarea', wide: true },
      { name: 'status', label: 'Status', type: 'select', options: STATUS('pending', 'approved', 'rejected'), default: 'pending', hrOnly: true }],
    rowActions: [{ label: 'Approve', hr: true, show: (r) => r.status === 'pending', run: payDecision('approved') },
      { label: 'Reject', hr: true, danger: true, show: (r) => r.status === 'pending', run: payDecision('rejected') }],
    sideLink: { label: 'Leave types', to: '/m/leave-types' } },

  { key: 'leave-types', title: 'Leave types', blurb: 'Yearly allowances and whether the leave is paid.', endpoint: 'leave-types', hrOnly: true, hidden: true,
    columns: [{ key: 'name', label: 'Name' }, { key: 'days_per_year', label: 'Days per year', num: true }, { key: 'paid', label: 'Paid', yesNo: true }],
    fields: [{ name: 'name', label: 'Name', required: true }, { name: 'days_per_year', label: 'Days per year', type: 'number', required: true },
      { name: 'paid', label: 'Paid leave', type: 'select', options: [{ value: 1, label: 'Paid' }, { value: 0, label: 'Unpaid' }], default: 1 }] },

  { key: 'payrolls', title: 'Payroll', blurb: 'Monthly payslips built from salary, overtime and unpaid leave.', endpoint: 'payrolls',
    columns: [{ key: 'period', label: 'Period' }, { key: 'employee.name', label: 'Employee' }, { key: 'basic', label: 'Basic', money: true },
      { key: 'allowances', label: 'Allowances', money: true }, { key: 'overtime_pay', label: 'Overtime', money: true },
      { key: 'deductions', label: 'Deductions', money: true }, { key: 'net_pay', label: 'Net pay', money: true }, { key: 'status', label: 'Status', badge: true }],
    fields: [employee, { name: 'period', label: 'Period (YYYY-MM)', required: true }, { name: 'basic', label: 'Basic', type: 'number', required: true },
      { name: 'allowances', label: 'Allowances', type: 'number', default: 0 }, { name: 'overtime_pay', label: 'Overtime pay', type: 'number', default: 0 },
      { name: 'deductions', label: 'Deductions', type: 'number', default: 0 }, { name: 'net_pay', label: 'Net pay', type: 'number', required: true },
      { name: 'status', label: 'Status', type: 'select', options: STATUS('draft', 'paid'), default: 'draft' }],
    toolbar: [{ label: 'Generate payroll', hr: true, primary: true, run: async () => {
      const period = prompt('Which month? (YYYY-MM)', new Date().toISOString().slice(0, 7));
      if (!period) return null;
      const { data } = await api.post('payrolls/generate', { period });
      return `Generated ${data.generated} payslips for ${data.period}.`;
    } }],
    rowActions: [{ label: 'Mark paid', hr: true, show: (r) => r.status === 'draft', run: (r) => api.post(`payrolls/${r.id}/pay`) }] },

  { key: 'job-openings', title: 'Job openings', blurb: 'Open roles and headcount.', endpoint: 'job-openings', hrOnly: true,
    columns: [{ key: 'title', label: 'Title' }, { key: 'department.name', label: 'Department' }, { key: 'vacancies', label: 'Vacancies', num: true },
      { key: 'status', label: 'Status', badge: true }],
    fields: [{ name: 'title', label: 'Job title', required: true, wide: true }, dept,
      { name: 'vacancies', label: 'Vacancies', type: 'number', default: 1, required: true },
      { name: 'status', label: 'Status', type: 'select', options: STATUS('open', 'closed'), default: 'open' },
      { name: 'description', label: 'Description', type: 'textarea', wide: true }] },

  { key: 'applicants', title: 'Applicants', blurb: 'Track candidates from application to hire.', endpoint: 'applicants', hrOnly: true,
    columns: [{ key: 'name', label: 'Name' }, { key: 'job_opening.title', label: 'Applying for' }, { key: 'email', label: 'Email' },
      { key: 'stage', label: 'Stage', badge: true }],
    fields: [{ name: 'job_opening_id', label: 'Job opening', type: 'select', remote: 'job-openings', remoteLabel: 'title', required: true },
      { name: 'name', label: 'Full name', required: true }, { name: 'email', label: 'Email', type: 'email', required: true }, { name: 'phone', label: 'Phone' },
      { name: 'stage', label: 'Stage', type: 'select', options: STATUS('applied', 'screening', 'interview', 'offer', 'hired', 'rejected'), default: 'applied' },
      { name: 'notes', label: 'Notes', type: 'textarea', wide: true }],
    rowActions: (() => {
      const order = ['applied', 'screening', 'interview', 'offer', 'hired'];
      return [{ label: 'Next stage', hr: true, show: (r) => order.includes(r.stage) && r.stage !== 'hired',
        run: (r) => api.put(`applicants/${r.id}`, { ...r, job_opening: undefined, stage: order[order.indexOf(r.stage) + 1] }) }];
    })() },

  { key: 'performance-reviews', title: 'Performance', blurb: 'Periodic reviews, ratings and goals.', endpoint: 'performance-reviews',
    columns: [{ key: 'period', label: 'Period' }, { key: 'employee.name', label: 'Employee' }, { key: 'rating', label: 'Rating (1–5)', num: true },
      { key: 'status', label: 'Status', badge: true }],
    fields: [{ ...employee }, { name: 'period', label: 'Review period (e.g. 2026-Q4)', required: true },
      { name: 'rating', label: 'Rating (1–5)', type: 'number', required: true }, { name: 'status', label: 'Status', type: 'select', options: STATUS('draft', 'submitted', 'acknowledged'), default: 'draft' },
      { name: 'goals', label: 'Goals', type: 'textarea', wide: true }, { name: 'comments', label: 'Comments', type: 'textarea', wide: true }] },
];
