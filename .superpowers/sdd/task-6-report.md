# Task 6: Aset Controller

**Status:** ✅ Complete
**Commit:** `feat: add Aset controller with search, filter, and sub-resources`

## Created

`backend/app/Http/Controllers/Api/AsetController.php`

## Endpoints

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/aset` | List with search/filter/paginate |
| POST | `/aset` | Create new aset |
| GET | `/aset/{id}` | Show with relations |
| PUT | `/aset/{id}` | Update (auto-records riwayat on status change) |
| DELETE | `/aset/{id}` | Soft delete |
| GET | `/aset/{id}/pemanfaatan` | Sub-resource: pemanfaatan |
| GET | `/aset/{id}/foto` | Sub-resource: foto |
| GET | `/aset/{id}/riwayat` | Sub-resource: riwayat |

## Notes

- Uses `Aset::search()` and `Aset::filter()` scopes (assumed defined in model)
- Riwayat auto-recorded when `status` field changes during update
- All sub-resources return paginated results
