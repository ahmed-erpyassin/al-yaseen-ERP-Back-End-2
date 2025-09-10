# Milestone Management System - Complete API Documentation

## ✅ **Complete "Add New Milestone" Implementation**

### 🎯 **Features Implemented**

All requested features have been successfully implemented:

1. **✅ Milestone Number**: Auto-generated sequential number per project
2. **✅ Project Number Dropdown**: Shows all project numbers with linked project names
3. **✅ Project Name Dropdown**: Shows all project names with linked project numbers
4. **✅ Milestone Name**: Manual input by user
5. **✅ Milestone Start Date**: Manual input by user
6. **✅ Milestone End Date**: Manual input by user
7. **✅ Status Dropdown**: Three statuses from project_milestones table
8. **✅ Progress**: Manual percentage input (0-100%)
9. **✅ Notes**: Manual input by user

---

## 🗄️ **Database Schema Updates**

### New Migration Created
**File**: `2025_09_10_000001_add_missing_fields_to_project_milestones_table.php`

**Fields Added**:
```sql
-- Sequential milestone number per project (auto-generated)
milestone_number INT NULL COMMENT 'Sequential milestone number per project'

-- Additional notes field
notes TEXT NULL COMMENT 'Additional notes for the milestone'

-- Index for uniqueness
INDEX project_milestone_number (project_id, milestone_number)
```

### Updated ProjectMilestone Model
**File**: `Modules/ProjectsManagment/app/Models/ProjectMilestone.php`

**New Features**:
- Auto-generation of milestone numbers
- Helper methods for status options
- Scopes for filtering
- Boot method for automatic numbering

---

## 🚀 **API Endpoints**

### **Main CRUD Operations**

#### 1. Create New Milestone
```
POST /api/v1/milestones
```

**Request Body**:
```json
{
    "project_id": 1,
    "name": "Phase 1 Completion",
    "start_date": "2025-01-15",
    "end_date": "2025-02-15",
    "status": "not_started",
    "progress": 0,
    "description": "Complete the first phase of the project",
    "notes": "Important milestone for project timeline"
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "milestone_number": 1,
        "project_id": 1,
        "name": "Phase 1 Completion",
        "start_date": "2025-01-15",
        "end_date": "2025-02-15",
        "status": "not_started",
        "progress": 0,
        "description": "Complete the first phase of the project",
        "notes": "Important milestone for project timeline",
        "project": {
            "id": 1,
            "code": "PRJ-2025-0001",
            "project_number": "P001",
            "name": "Website Development"
        }
    },
    "message": "Milestone created successfully"
}
```

#### 2. Get All Milestones
```
GET /api/v1/milestones
```

**Query Parameters**:
- `per_page`: Number of results per page (default: 15)
- `project_id`: Filter by specific project
- `status`: Filter by status (not_started, in_progress, completed)
- `search`: Search in name, description, notes, or project details
- `sort_by`: Sort field (id, milestone_number, name, start_date, end_date, status, progress, created_at)
- `sort_order`: Sort direction (asc, desc)

#### 3. Get Single Milestone
```
GET /api/v1/milestones/{id}
```

#### 4. Update Milestone
```
PUT /api/v1/milestones/{id}
```

#### 5. Delete Milestone (Soft Delete)
```
DELETE /api/v1/milestones/{id}
```

---

### **Helper Endpoints for Dropdowns**

#### 1. Get Projects for Dropdown
```
GET /api/v1/milestones/projects/list
```

**Response** (Includes both project numbers and names):
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "code": "PRJ-2025-0001",
            "project_number": "P001",
            "name": "Website Development",
            "display_name": "Website Development (P001)",
            "display_number": "P001 - Website Development"
        },
        {
            "id": 2,
            "code": "PRJ-2025-0002",
            "project_number": "P002",
            "name": "Mobile App Development",
            "display_name": "Mobile App Development (P002)",
            "display_number": "P002 - Mobile App Development"
        }
    ],
    "message": "Projects retrieved successfully"
}
```

#### 2. Get Status Options
```
GET /api/v1/milestones/statuses/list
```

**Response**:
```json
{
    "success": true,
    "data": [
        {"value": "not_started", "label": "Not Started"},
        {"value": "in_progress", "label": "In Progress"},
        {"value": "completed", "label": "Completed"}
    ],
    "message": "Milestone statuses retrieved successfully"
}
```

#### 3. Generate Next Milestone Number
```
POST /api/v1/milestones/generate-number
```

**Request Body**:
```json
{
    "project_id": 1
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "milestone_number": 3
    },
    "message": "Next milestone number generated successfully"
}
```

#### 4. Get Project Milestones
```
GET /api/v1/milestones/project/{projectId}
```

---

## 🎨 **Frontend Integration Examples**

### **Create Milestone Form**

```javascript
// Get projects for dropdown
const getProjects = async () => {
    const response = await fetch('/api/v1/milestones/projects/list', {
        headers: { 'Authorization': `Bearer ${token}` }
    });
    const data = await response.json();
    return data.data;
};

// Get status options
const getStatusOptions = async () => {
    const response = await fetch('/api/v1/milestones/statuses/list', {
        headers: { 'Authorization': `Bearer ${token}` }
    });
    const data = await response.json();
    return data.data;
};

// Generate milestone number for selected project
const generateMilestoneNumber = async (projectId) => {
    const response = await fetch('/api/v1/milestones/generate-number', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ project_id: projectId })
    });
    const data = await response.json();
    return data.data.milestone_number;
};

// Create milestone
const createMilestone = async (milestoneData) => {
    const response = await fetch('/api/v1/milestones', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            project_id: milestoneData.projectId,
            name: milestoneData.name,
            start_date: milestoneData.startDate,
            end_date: milestoneData.endDate,
            status: milestoneData.status,
            progress: milestoneData.progress || 0,
            description: milestoneData.description,
            notes: milestoneData.notes
            // milestone_number is auto-generated
        })
    });
    return response.json();
};
```

### **Project Selection with Linked Names/Numbers**

```javascript
// Handle project number selection - show related project name
const handleProjectNumberChange = (selectedProjectId, projects) => {
    const selectedProject = projects.find(p => p.id === selectedProjectId);
    if (selectedProject) {
        // Auto-populate project name field
        setProjectName(selectedProject.name);
        // Generate milestone number for this project
        generateMilestoneNumber(selectedProjectId).then(number => {
            setMilestoneNumber(number);
        });
    }
};

// Handle project name selection - show related project number
const handleProjectNameChange = (selectedProjectId, projects) => {
    const selectedProject = projects.find(p => p.id === selectedProjectId);
    if (selectedProject) {
        // Auto-populate project number field
        setProjectNumber(selectedProject.project_number);
        // Generate milestone number for this project
        generateMilestoneNumber(selectedProjectId).then(number => {
            setMilestoneNumber(number);
        });
    }
};
```

---

## ✅ **Validation Rules**

### **Create Milestone Validation**
- `project_id`: Required, must exist in projects table
- `name`: Required, max 255 characters
- `start_date`: Required, must be today or future date
- `status`: Required, must be one of: not_started, in_progress, completed
- `milestone_number`: Optional (auto-generated if not provided), must be unique per project
- `end_date`: Optional, must be after or equal to start_date
- `progress`: Optional, must be 0-100
- `description`: Optional, max 1000 characters
- `notes`: Optional, max 2000 characters

### **Update Milestone Validation**
- All fields are optional for updates
- Same validation rules apply when fields are provided
- Milestone number uniqueness checked excluding current milestone

---

## 🔒 **Security Features**

- ✅ **Authentication Required**: All endpoints require valid authentication token
- ✅ **Company-Level Access Control**: Users can only access milestones from their company
- ✅ **Project Ownership Validation**: Projects must belong to user's company
- ✅ **Input Validation**: Comprehensive validation for all fields
- ✅ **SQL Injection Protection**: Using Eloquent ORM with parameter binding
- ✅ **Soft Delete Support**: Deleted milestones are soft-deleted with audit trail

---

## 📊 **Database Structure**

### **project_milestones Table**
```sql
CREATE TABLE project_milestones (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    company_id BIGINT NOT NULL,
    branch_id BIGINT NOT NULL,
    fiscal_year_id BIGINT NOT NULL,
    project_id BIGINT NOT NULL,
    milestone_number INT NULL,           -- NEW: Auto-generated sequential number
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    progress DECIMAL(5,2) DEFAULT 0,
    notes TEXT NULL,                     -- NEW: Additional notes field
    created_by BIGINT NULL,
    updated_by BIGINT NULL,
    deleted_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_company_project (company_id, project_id),
    INDEX idx_project_milestone_number (project_id, milestone_number),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
);
```

---

## 🎯 **Summary**

✅ **All Requirements Implemented**:
- Sequential milestone numbering per project (auto-generated)
- Project number dropdown with linked project names
- Project name dropdown with linked project numbers
- Manual milestone name input
- Manual start/end date inputs
- Status dropdown with three options
- Progress percentage input (0-100%)
- Notes field for additional information

✅ **Additional Features**:
- Comprehensive validation
- Search and filtering capabilities
- Sorting by all fields
- Soft delete with audit trail
- Company-level access control
- Auto-generation of milestone numbers
- Helper endpoints for dropdown data

The complete milestone management system is now ready for production use!
