# Projects Module Routes Update Summary

## Overview
Updated all route names in the Projects Management module to ensure uniqueness and prevent conflicts with other modules in the ERP system.

## Changes Made

### 1. Route Name Prefix
- **Old**: Generic names like `projects.index`, `tasks.store`, etc.
- **New**: Prefixed with `project-mgmt.` for all routes (e.g., `project-mgmt.projects.list`, `project-mgmt.tasks.create`)

### 2. Route Path Updates
Updated route prefixes to be more specific and avoid conflicts:

| Old Prefix | New Prefix | Description |
|------------|------------|-------------|
| `tasks` | `project-tasks` | Task management routes |
| `milestones` | `project-milestones` | Milestone management routes |
| `resources` | `project-resources` | Resource management routes |
| `documents` | `project-documents` | Document management routes |
| `project-financials` | `project-finance` | Financial management routes |
| `project-risks` | `project-risk-management` | Risk management routes |

### 3. Route Name Standardization
Standardized route naming convention:

| Operation Type | Old Pattern | New Pattern |
|----------------|-------------|-------------|
| List/Index | `.index` | `.list` |
| Create/Store | `.store` | `.create` |
| Show/Details | `.show` | `.details` |
| Update | `.update` | `.modify` |
| Delete | `.destroy` | `.remove` |
| Search | `.search` | `.advanced-search` |
| Sort | `.sort` | `.apply-sort` |
| Restore | `.restore` | `.restore-[entity]` |
| Force Delete | `.force-delete` | `.permanent-delete` |
| Trashed | `.trashed` | `.deleted-[entities]` |

### 4. Specific Route Updates

#### Projects Routes (api/v1/projects)
- `projects.index` → `project-mgmt.projects.list`
- `projects.store` → `project-mgmt.projects.create`
- `projects.show` → `project-mgmt.projects.details`
- `projects.customers` → `project-mgmt.projects.customer-options`
- `projects.calculate-vat` → `project-mgmt.projects.vat-calculation`

#### Task Routes (api/v1/project-tasks)
- `tasks.index` → `project-mgmt.tasks.list`
- `tasks.my-tasks` → `project-mgmt.tasks.assigned-to-me`
- `tasks.daily-due` → `project-mgmt.tasks.due-today`
- `tasks.overdue` → `project-mgmt.tasks.overdue-tasks`
- `tasks.upload-document` → `project-mgmt.tasks.upload-document`

#### Milestone Routes (api/v1/project-milestones)
- `milestones.index` → `project-mgmt.milestones.list`
- `milestones.generate-number` → `project-mgmt.milestones.number-generation`
- `milestones.by-project` → `project-mgmt.milestones.by-project`

#### Resource Routes (api/v1/project-resources)
- `resources.index` → `project-mgmt.resources.list`
- `resources.calculate-allocation` → `project-mgmt.resources.allocation-calculation`
- `resources.calculate-percentage` → `project-mgmt.resources.percentage-calculation`

#### Document Routes (api/v1/project-documents)
- `documents.index` → `project-mgmt.documents.list`
- `documents.download` → `project-mgmt.documents.file-download`
- `documents.categories` → `project-mgmt.documents.category-options`

#### Financial Routes (api/v1/project-finance)
- `project-financials.index` → `project-mgmt.finance.list`
- `project-financials.by-reference-type` → `project-mgmt.finance.by-reference-type`
- `project-financials.by-date-range` → `project-mgmt.finance.by-date-range`

#### Risk Management Routes (api/v1/project-risk-management)
- `project-risks.index` → `project-mgmt.risks.list`
- `project-risks.statistics` → `project-mgmt.risks.risk-statistics`
- `project-risks.impact-options` → `project-mgmt.risks.impact-options`
- `project-risks.probability-options` → `project-mgmt.risks.probability-options`

## Benefits

1. **No Route Conflicts**: All route names are now unique across the entire application
2. **Clear Namespace**: Easy to identify which routes belong to the Projects module
3. **Consistent Naming**: Standardized naming convention across all routes
4. **Better Organization**: More descriptive route paths and names
5. **Future-Proof**: Prevents conflicts when adding new modules

## Verification

All routes have been tested and verified:
- Total of 139 project management routes successfully registered
- No naming conflicts with other modules (Inventory, Companies, HR, Suppliers, etc.)
- All route names follow the new `project-mgmt.*` pattern
- Route paths are more descriptive and specific

## Files Modified

- `Modules/ProjectsManagment/routes/api.php` - Complete route restructuring

## Next Steps

When updating frontend applications or API clients, ensure they use the new route names and paths as documented above.
