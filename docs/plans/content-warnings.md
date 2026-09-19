# Content Warnings and Content Boundaries

## Agreed behavior

- Books declare public Content Warnings; Beta Reader Profiles keep private Content Boundaries.
- Both choose from the same shared topics: Sexual content, Sexual violence, Abuse, Violence, Self-harm, Suicide, Eating disorders, Substance use, and Discrimination.
- Selection is optional on both sides. Existing Books start with no warnings declared. An empty Book selection is displayed as "No content warnings declared", not as a guarantee about its content.
- A Listing is excluded from a Beta Reader's Feed when its Book has a Content Warning identical to one of that reader's Content Boundaries. Related topics do not match automatically; Authors select every applicable warning.
- Changes to a Book's warnings or a reader's boundaries affect future Feed results immediately. Existing Interests and Matches remain intact.
- A Feed card already open in a browser may still submit Interest after its Book's warnings change; this edge case does not require rejection.
- Book warnings are visible wherever the Book is shown, including its Feed card. Reader boundaries are visible only to that reader in their profile form.
- The first release has no in-product topic suggestion flow.

## Implementation touchpoints

- Book and Beta Reader Profile create/edit forms, validation, persistence, and display.
- Feed query filters Listings using the shared topic identifiers.
- Existing Books and Beta Reader Profiles begin with empty selections.
