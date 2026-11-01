# Staff role CRUD permissions

The existing Role Management module remains the source of staff permissions. Its
`role_menus` record stores a JSON map keyed by the exact sidebar breadcrumb, for
example `Academic > Examination & Grading > Class 11-12 Marksheet`, with the
existing `view`, `add`, `edit`, and `delete` flags. The `add` flag is the
Role Management name for Create. This does not replace or reinterpret the
separate Spatie permissions used elsewhere in the application.

`App\Services\PermissionService` is the shared resolver for the sidebar, Blade
controls, and backend checks. Permission results are cached on the current
request only. Role updates clear the affected role's request cache. Staff are
identified through the existing staff guard/portal session; a staff role named
`Admin` does not grant the Admin bypass. Admin and Student authentication flows
are not changed by this permission convention.

## Integrating a module

1. Add the module leaf to `config/sidebar.php` and use the exact breadcrumb
   title in Role Management. Keep the existing route names and staff route
   targets where applicable.
2. Keep the existing authentication middleware. Staff requests pass through
   `CheckMenuPermission`, which resolves the current route to a CRUD action and
   menu using, in order, explicit route metadata, existing module aliases,
   sidebar route targets, and unique controller-to-menu mappings. If a
   controller serves multiple menu items or an endpoint cannot be inferred
   reliably, add explicit permission middleware to that endpoint:

   ```php
   Route::get('/things', [ThingController::class, 'index'])
       ->middleware('check.permission:view,Operations > Things');
   Route::post('/things', [ThingController::class, 'store'])
       ->middleware('check.permission:add,Operations > Things');
   Route::put('/things/{thing}', [ThingController::class, 'update'])
       ->middleware('check.permission:edit,Operations > Things');
   Route::delete('/things/{thing}', [ThingController::class, 'destroy'])
       ->middleware('check.permission:delete,Operations > Things');
   ```

   The route middleware is important for ambiguous legacy routes and actions
   that do not follow conventional HTTP methods/names. Do not assign `view` to
   mutation endpoints; each endpoint must carry its own required action. The
   resolver fails closed when it cannot find a matching permission.
3. Wrap each server-rendered action with the shared Blade directives. Pass the
   menu breadcrumb or a registered module/route alias:

   ```blade
   @canAdd('Operations > Things')
       <a href="{{ route('things.create') }}">Add</a>
   @endcanAdd

   @canEdit('Operations > Things')
       <a href="{{ route('things.edit', $thing) }}">Edit</a>
   @endcanEdit

   @canDelete('Operations > Things')
       <button type="submit">Delete</button>
   @endcanDelete
   ```

   `@canCreate` is an alias for the existing `add` permission. `@canView` and
   `@canAction('action', $menu)` are also available. Wrap action-column headers
   and cells consistently; hide the whole column when none of its actions are
   available. AJAX-rendered controls must be returned through permission-aware
   Blade partials or use the same directives. Do not build unauthorized action
   HTML in JavaScript.
4. Ensure AJAX and bulk-action endpoints use the same route checks. Hiding a
   button is only a presentation rule; backend middleware is the security
   boundary and returns HTTP 403 (JSON for JSON/AJAX requests).

The permission layer can infer conventional route/action patterns and unique
controller modules, but it cannot infer the meaning of arbitrary custom
endpoints or hide unannotated HTML. Integrate every new module using the steps
above rather than assuming a new sidebar item alone protects its controls.

## Verification

Run the focused regression suite with:

```text
php artisan test --filter=RoleMenuPermissionTest
```

It exercises all eight requested CRUD combinations, sidebar visibility,
Blade action visibility, route resolution, direct staff denial, JSON/AJAX
denial, staff login redirection, and the staff/Admin bypass boundary.
