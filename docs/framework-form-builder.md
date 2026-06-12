# Catalyst Framework FormBuilder

## Purpose

`Catalyst\Framework\Form\FormBuilder` is the global fluent API for reusable
form contracts. It normalizes fields, sections, actions, model/default values,
validation errors, multipart state and safe HTML attributes.

## Runtime Owners

| Concern | Owner |
|---|---|
| Form configuration and server-side normalization | `Catalyst\Framework\Form\FormBuilder` |
| Render contract | `Catalyst\Framework\Form\FormBuilderViewModel` |
| Shared templates | `boot-core/template/components/_form-builder.phtml` and `components/form-builder/` |
| Dependencies, repeaters and autosave | `public/assets/js/catalyst/forms/builder.js` through the central UI runtime |
| Browser constraint validation | `public/assets/js/catalyst/forms/validation.js` |
| Event-driven submissions and field errors | `public/assets/js/catalyst/forms/form-handler.js` |
| Shared styles | `public/assets/css/catalyst/form-builder.css` |

## Contracts

Fields support hidden, text-compatible inputs, textarea, select, multiple
select, checkbox, file and repeater controls. Values resolve from old input,
model data, defaults and field configuration in that order. Validation errors
remain owned by the framework validation helpers and are projected into the
ViewModel.

Form and field attribute values are escaped. Invalid attribute names and inline
event handlers are discarded. POST-compatible forms include CSRF state, custom
HTTP methods use `_method`, and file fields enable multipart encoding.

Client-side dependencies disable hidden controls so stale values are not
submitted. Repeater additions trigger a central runtime rescan. Autosave uses
local storage only when explicitly enabled and excludes passwords, files,
method overrides and CSRF tokens.

## Consumption

Controllers and factories build `FormBuilder::make()->...->toArray()` and views
render `components._form-builder`. Internal ViewModel details are not a
controller contract.
