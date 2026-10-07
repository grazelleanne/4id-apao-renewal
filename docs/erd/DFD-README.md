# APAO data flow diagram

Open `project-dfd.drawio` through **File > Open From > Device**.

For only the Level 1 diagram, open `project-dfd-level-1.drawio`. The matching Mermaid source is `project-dfd-level-1.mmd`. This diagram decomposes the system into seven numbered processes and their data exchanges.
The Level 1 overview uses shared Staff and Administrator entities and one node per data store. Personnel and inspection processes supply notification data; system processes supply action details to audit logging. All nodes belong to one connected diagram. Individual process pages retain repeated actors and stores for readability.

The black-and-white document contains a context diagram, a Level 1 overview, and seven individual process pages. Use the individual pages for readable labels and less connector congestion.

- Rounded boxes represent users or the named sources of data.
- Circles represent system processes, matching the supplied reference.
- Divided rectangles represent database tables in draw.io. Mermaid uses its supported double-sided rectangle shape for stores.
- Arrows represent named data flows; they are not workflow sequences or ERD cardinalities.

These diagrams use the reference's DFD shapes while retaining black-and-white styling. Decision diamonds belong in the separate workflow flowchart; the DFD arrows describe data movement.

The context diagram shows the whole system exchanging data with Staff and Administrator. The Level 1 diagrams show authentication/account management, personnel management, inspection/renewal, property receipts, notifications, reports, and audit activity. Users and stores are repeated across process groups for readability; repeated labels refer to the same actor or table.

The audit process receives activity data from the other system processes as well as administrator filters. The combined source box on that process page names both sources; system processes are internal sources, not a third external user.

Based on application routes and queries in `public/index.php` and the local schema. This is a main data-flow model, not an exhaustive model of every endpoint or a verification of the live database. Legacy OTP storage and schema-only tables are not shown as active flows. Notifications may originate from registration, inspection and renewal actions; the notification process groups their storage and retrieval.

For Mermaid import, paste `project-dfd-context.mmd` or `project-dfd-level-1.mmd`. Individual diagrams are `project-dfd-process-1.mmd` through `project-dfd-process-7.mmd`.

Regenerate with:

```powershell
node docs/erd/generate-dfd.cjs
```
