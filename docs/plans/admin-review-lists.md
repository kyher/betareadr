# Admin Review Lists

## Agreed behavior

- Administrators can browse five paginated lists: Users, Author Profiles, Beta Reader Profiles, Books, and Beta Reading Listings. Existing administrator authentication protects every list.
- Each list shows 12 records per page, newest first, with a stable tie-breaker. Search and filters persist when paging.
- Users can be searched by name or email. Rows show name, email, creation date, and associated Author and Beta Reader Profile names. Other private account details are omitted.
- Author Profiles and Beta Reader Profiles have separate lists. Each can be searched by display name or handle. Rows show display name, handle, associated User, creation date, and a link to the public profile page. Private Content Boundaries are omitted.
- Books can be searched by title. Rows show title, Author Profile, Book Language, Content Warnings, full description, and creation date.
- Beta Reading Listings can be searched by Book title, Author Handle, or exact Listing ID. Rows show Listing ID, Book, open or closed status, word count, full Reading Brief, and creation date. The list includes closed Listings and has an all/open/closed filter that defaults to all.
- The admin header links to each list. Dashboard count cards link to their corresponding lists; both Listing counts lead to the Listing list.
- Administrators can permanently Remove User records from the Users list. Removal follows the Delete User rules: it deletes both Profiles, Books, Listings, Interests, Passes, all Matches and Conversations involving that User, including the other participant's messages. It retains no reason or audit record and does not prevent re-registration.
- Administrators can permanently remove Books and Beta Reading Listings from their respective lists. Removal deletes their dependent Listings or Interests, Passes, Matches, and Conversations. A Book or Listing with an active Match cannot be removed; end the Match first.
- Author Profile and Beta Reader Profile lists provide no removal actions. All removals require an explicit browser confirmation and report a success or active-Match error.

## Implementation touchpoints

- Add list routes and removal routes under the existing administrator guard, with removal actions using the existing User, Book, and Listing deletion behavior. Render the lists using the shared pagination component.
- Keep Book and Listing review content on their lists because neither has a public detail route.
- Add navigation and count-card links to the existing admin layout and dashboard.
