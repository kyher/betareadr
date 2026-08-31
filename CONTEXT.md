# BetaReadr

BetaReadr connects authors with beta readers who can provide pre-publication feedback on written work.

## Language

**User**:
A person with a BetaReadr account who privately owns authentication and contact details and may create an Author Profile, a Beta Reader Profile, or both.
_Avoid_: Member, account

**Author**:
A User participating through an Author Profile to seek beta-reader feedback on one or more books.
_Avoid_: Writer, creator

**Beta Reader**:
A User participating through a Beta Reader Profile to find pre-publication books and offer feedback.
_Avoid_: Reviewer, critic, potential beta reader

**Author Profile**:
The single optional public identity through which a User presents themselves as an Author and manages their Books and Beta Reading Listings. A User may have at most one; it becomes public as soon as the User creates it with a display name and unique Author Handle, while its avatar and bio are optional and independent of the User and Beta Reader Profile.
_Avoid_: Author account

**Author Handle**:
The unique, URL-safe name identifying an Author Profile, distinct from the Author's non-unique display name. Handle changes are not currently supported.
_Avoid_: Username, profile name

**Beta Reader Profile**:
The single optional public identity through which a User presents their reading interests, boundaries, experience, and availability to Authors. A User may have at most one; it becomes public as soon as the User creates it with a display name and unique Beta Reader Handle, while its avatar and bio are optional and independent of the User and Author Profile.
_Avoid_: Reader account

**Beta Reader Handle**:
The URL-safe name uniquely identifying a Beta Reader Profile among Beta Reader Profiles, distinct from the Beta Reader's non-unique display name and any Author Handle belonging to the same User. Author and Beta Reader Handles occupy separate namespaces, and handle changes are not currently supported.
_Avoid_: Username, profile name, Author Handle

**Book**:
A publicly visible, enduring written work belonging to an Author Profile, for which beta-reader feedback may be sought through one or more recruitment rounds. A Book is public as soon as it is created.
_Avoid_: Project, manuscript, listing

**Beta Reading Listing**:
A distinct recruitment round through which an Author seeks Beta Readers for a Book. A Book may have multiple listings over time, but at most one may be open at once; each Listing is open as soon as it is created and remains so until closed.
_Avoid_: Book, post, campaign, request

**Reading Brief**:
The Listing-specific description of the draft being offered and the feedback the Author is seeking from Beta Readers.
_Avoid_: Book description, listing description

**Content Warning**:
A declared type of potentially distressing or sensitive material present in a Book.
_Avoid_: Trigger, tag

**Content Boundary**:
A type of material a Beta Reader has chosen not to encounter. Listings for a Book with a matching Content Warning are excluded from that reader's feed.
_Avoid_: Preference, dislike

**Close**:
An Author's reversible removal of a Beta Reading Listing from Beta Readers' feeds. A closed Listing accepts no new Interest while preserving its existing records.
_Avoid_: Remove, delete

**Delete Book**:
An Author's permanent removal of a Book, its Listings, and unresolved Interests. Existing Matches and Conversations survive with only a minimal deleted-book reference.
_Avoid_: Close, archive

**Interest**:
A Beta Reader's indication that they want to participate in a Beta Reading Listing presented in their feed. It remains subject to the Author's approval.
_Avoid_: Like, swipe, application

**Pass**:
A Beta Reader's indication that they do not want to participate in a Beta Reading Listing presented in their feed.
_Avoid_: Dislike, rejection

**Decline**:
An Author's decision not to accept a Beta Reader's Interest in a specific Beta Reading Listing. It closes the Interest without notifying the Beta Reader or giving a reason.
_Avoid_: Denial, rejection

**Match**:
A connection created when an Author approves a Beta Reader's Interest in a specific Beta Reading Listing.
_Avoid_: Friendship, pairing

**Conversation**:
The private communication between the Author and Beta Reader belonging to one Match. Participants may use it to arrange external manuscript delivery without automatically exposing contact details.
_Avoid_: Chat room, thread

**Block**:
A User-level safety action that prevents two Users from discovering or contacting each other through either profile and ends their existing Matches. Related Conversation history is retained for moderation but hidden from both participants.
_Avoid_: Mute, profile block
