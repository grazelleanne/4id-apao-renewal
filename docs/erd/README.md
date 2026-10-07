# APAO project ERD

Based on `database/schema.sql`, including the new-staff first-login password flag.
This describes the project's schema, not an independently inspected live Render database.

## Open in draw.io

Open `project-erd.drawio` using **File > Open From > Device** in diagrams.net.
The tables, PK/FK cells and crow's-foot connectors are editable native draw.io shapes.
Each native table has a key column on the left and field names on the right, matching the reference table. Primary-key rows come first, followed by foreign keys, unique keys and other fields. Primary-key field names are underlined. The actual schema field `id` is retained; it is not renamed to `UniqueID`. Data types and nullability remain available in the Mermaid files.
There are three tabs:

- **Overview + logical references:** all 11 tables, selected important columns, all 10 enforced foreign keys, and 6 unenforced references.
- **Full schema:** all 212 columns and the 10 enforced foreign keys.
- **Cardinality guide:** reference examples for 1:1, 1:0..1, 1:0..N, 1:1..N, 0..1:0..N and many-to-many. These examples are not additional tables or relationships in this project.

The diagrams use black backgrounds and white text, table borders and connectors. Solid draw.io connectors mean an actual foreign-key constraint; dashed connectors mean an unenforced reference. Connector labels also state the participating field and the minimum/maximum cardinality.

## Mermaid import

Use **Arrange > Insert > Advanced > Mermaid** and paste the contents of:

- `project-erd.mmd` for the complete physical schema.
- `project-erd-logical.mmd` for the complete schema plus inferred links.

Choose **Diagram** for editable shapes if your draw.io version offers it; **Image** imports a rendered SVG instead.

For the black table / white text appearance, open `project-erd.drawio` directly. Some draw.io Mermaid-to-editable-shapes conversions discard Mermaid CSS and produce dark text on dark rows. The Mermaid files now explicitly style attribute text, headers, rows and connectors; choose **Image** to preserve rendered styling. Re-import after changing the source: existing imported shapes do not update automatically.

Mermaid notation:

- `||`: exactly one.
- `|o` or `o|`: zero or one.
- `}o` or `o{`: zero or many.
- `}|` or `|{`: one or many.
- `..`: non-identifying relationship. It does **not** mean there is no foreign key. All relationships here are non-identifying because the child's primary key does not contain the parent's primary key.
- `PK`: primary key; `FK`: enforced foreign key; `UK`: unique key.

Read a relationship from the entity at either end. For example, `personnel ||..o{ inspections` means each inspection belongs to exactly one personnel record, while a personnel record may have zero or many inspections.

## Important schema details

- There is no enforced one-to-one or direct many-to-many relationship. The cardinality guide demonstrates these symbols without inventing project relationships.
- `property_acknowledgement_receipts.previous_par_id` is nullable and is **not unique**. Therefore, a receipt can reference zero or one previous receipt, and that previous receipt can be referenced by zero or many receipts. The database does not enforce a single replacement chain.
- `renewal_transactions.source_history_id` is nullable and unique, but has no FK constraint. Its inferred relationship to `renewal_history` is optional one-to-one. Orphan references are possible.
- `password_resets_otp.email` is a primary key and `users.email` is unique. Matching emails imply an optional one-to-one reference, but no FK enforces the match. This legacy OTP table remains in the schema even though login OTP is disabled.
- Other unenforced references are `inspections.inspected_by_user_id`, `renewal_transactions.processed_by_user_id`, `notifications.personnel_id`, and `renewal_history.item_number`. Their optional parent symbols acknowledge that an orphan reference is possible.
- `personnel_data_conflicts` has a **composite** unique constraint on `(personnel_id, field)`. Neither column is independently unique.
- `ics_settings` has no FK relationships and intentionally appears disconnected.
- Firearms are stored as columns and receipt equipment as JSON; there is no separate firearms or equipment table.
- Archive Data is a view of soft-archived personnel (`archived_at`/`is_archived`), not a separate table. Reports and RPCSP are derived outputs, not separate database tables.

## Project flowchart

Open `project-flowchart.drawio` directly in draw.io, or import `project-flowchart.mmd` using the Mermaid importer. Both use black and white.
The draw.io flowchart has three spaced pages: **Login**, **Staff workflow**, and **Admin workflow**. Each uses larger boxes and 18px text. For separate Mermaid imports, use `project-flowchart-login.mmd`, `project-flowchart-staff.mmd`, or `project-flowchart-admin.mmd`; these are easier to read than importing the entire workflow at once.
The flowchart shows authentication, the first-login password requirement for new staff, staff registration and inspection submission, admin inspection review, renewal history, notifications, receipts, reports and sign-out. Admin account management, archive/restore and audit filtering are shown as separate dashboard activities. This is the main workflow, not every validation or error branch.

## Regenerate all diagrams

Run from the project directory:

```powershell
node docs/erd/generate-erd.cjs
```

The generator checks table counts, column references and foreign keys and writes both Mermaid files, the draw.io document and `schema-summary.json`.

References: [Mermaid ER syntax](https://mermaid.js.org/syntax/entityRelationshipDiagram.html), [draw.io Mermaid import](https://www.drawio.com/docs/manual/insert/insert-mermaid/).
