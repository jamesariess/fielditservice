FIELD IT SERVICE HUB - UPLOAD PACKAGE
=====================================
Built: 2026-10-01  (V8)

WHAT TO UPLOAD (paths are relative to your app root - the folder that contains
public/, api/, includes/. On InfinityFree that folder is htdocs/)

  REQUIRED - upload ALL SIX. Miss one and the page shows
  "Failed to load resource: 404" in the browser console.

    public/pages/tickets.php              overwrite
    public/assets/js/ticket-scan.js       overwrite (or add)
    public/assets/js/app.js               overwrite
    public/assets/css/workspace-refresh.css   overwrite (or add)  <-- NEW in V3
    public/assets/js/ticket-workspace.js      overwrite (or add)  <-- NEW in V3
    public/pages/admin/statistics.php         overwrite            <-- NEW in V5

  DATABASE (V7) - only when the database itself has to be backed up or fixed,
  not for the pages above

    database/backups/README-BACKUP-AND-RESTORE.txt   read this one first
    database/backups/sync-live-database.sql          import it in phpMyAdmin:
                                                     it only ever ADDS missing
                                                     tables, columns and
                                                     reference rows, so it
                                                     cannot delete tickets
    database/db-tool.php                             optional, command line
                                                     only (status / backup /
                                                     repair / prepare)

  RECOMMENDED - fixes two host-specific 404s that only appear off XAMPP

    public/pages/.htaccess                overwrite
    api/upload.php                        overwrite

  OPTIONAL (browser self-tests, safe to skip on the live site)

    tests/ticket-scan-parse.html
    tests/ticket-scan-apply.html
    tests/ticket-filter-status.html      <-- NEW in V4
    tests/ticket-drawer-mobile.php       <-- NEW in V4 (needs PHP; this one
                                             prints its result in the browser)
    tests/statistics-layout.php          <-- NEW in V5 (needs PHP)
    tests/statistics-labels.php          <-- NEW in V5 (run it with php, not
                                             in a browser)

Easiest safe route: upload the whole _fixstage folder contents to htdocs/ and
let it overwrite. Nothing here needs a database change or a config change.


V8 - PROFILE AT THE TOP + PROFILE PICTURE ("My Profile" left the sidebar)
  What changed for the technician:
    - The header now ends with the account avatar (beside the log out icon). Tap
      it and the profile opens from any page.
    - "My Profile" is gone from the sidebar. The block at the bottom of the
      sidebar (name + role) still opens the profile, and it lights up while the
      profile page is open, so nothing is lost.
    - The profile page has "Change photo" and "Remove". The picture shows up in
      the header avatar, the sidebar block and on the profile card on every
      page, for that account only.
    - A phone photo is squared and shrunk to 512px in the browser before it is
      uploaded, so a 5MB camera shot goes up as a small JPEG in a second or
      two; the server also checks the real file type (JPG/PNG/WebP/GIF, max
      8MB) and keeps only one picture per account (the old file is deleted).

  WHERE THE PICTURE IS STORED: users.avatar_url (a column the database already
  has, so there is no SQL to run) and the file in public/uploads/avatars/.
  That folder must be writable on the host; if it does not exist the upload
  endpoint creates it.

  FILES FOR THIS ONE (all four, plus the folder):
    includes/layout_header.php              REQUIRED (header avatar, sidebar change)
    includes/Auth.php                       REQUIRED (the picture is kept in the session)
    public/pages/profile.php                REQUIRED (Change photo / Remove)
    api/profile/avatar.php                  REQUIRED (upload + remove endpoint)
    public/assets/css/app.css               recommended (sidebar "active" highlight)
    public/uploads/avatars/                 the folder, writable (created automatically)


V7 - THE WORK ORDER SCANNER: "IT DOES NOT READ MY TICKET NUMBER" + THE ADDRESS
  Two separate faults, both fixed in public/assets/js/ticket-scan.js.

  1. TICKET NUMBER
     The parser only looked at what followed the words "Work Order". Your
     sheet prints the number beside SD, and it prints it twice ("Order No.:
     SD1041040" above "Work Order No.: SD1041036"), so whenever OCR split or
     misread that line the field stayed empty and the panel said "not read -
     fill in" - exactly what you saw in the screenshot.

       - The ticket number is now the SD number at the very top of the sheet,
         which is your rule. OCR slips inside it are repaired, so SD1O41O36
         still reads as 1041036.
       - When the sheet carries two SD numbers, the other one is offered right
         under the field as a tap-to-use chip instead of being thrown away.
       - If the first pass misses the ticket number, the photo is read a second
         time in single-column mode and both results are merged, so a tilted or
         edge-cropped photo gets a second chance instead of coming back empty.
       - SD264613131 fills 264613131: "SD" is the prefix the form adds itself.
         Digit slips (SD2646l3l3l) and spaced-out reads (SD 264 613 131) come out
         as 264613131 too, and a word glued to the end (SD264613131Sched) no
         longer drags extra characters in.
       - If the number was read but the "SD" beside it was not, the number is
         still offered as a tap-to-use chip, the panel prints "What the scan
         read at the top: ..." so a bad photo is obvious instead of silent, and
         "Copy what was read" copies the raw OCR text to report a misread.

  2. ADDRESS
     The printed right-hand column ("Contact Nos.", "T 09178502783", "Customer
     Request / Instruction:") lands on the same OCR line as the address, and
     the old code glued the two together - hence "T 09178502783 GIF. Power
     Plant Mall ... Plaza Drive Customer Brgy Poblacion ...".

       - Phone numbers and their stray "T" marker are dropped.
       - The half-read label word ("Customer") is no longer kept.
       - The block stops at the printed labels and at OCR noise lines instead
         of swallowing them.
       - "GIF." is corrected back to "G/F,".
       - The company name loses the trailing circled glyph ("Banco De Oro (2)"
         -> "Banco De Oro").

  Verified against your own sheet (tests/ticket-scan-parse.html, every check
  PASS): ticket 1041040 (top SD) with 1041036 offered, company "Banco De Oro",
  address "G/F, Power Plant Mall, Rockwell Center, Plaza Drive, Brgy Poblacion,
  Makati, Metro Manila, NCR", unit "Samsung ED40C", serial ZXVCHMFD600053B,
  device type "Monitor" (read from "For dismantling of LFD"), task "Availability
  10PM". Upload BOTH public/assets/js/ticket-scan.js (the detection) and
  public/pages/tickets.php (the new "Copy what was read" button lives in the
  page). The two tests are optional.


V6 - "I CAN'T SCROLL IN MOBILE VERSION" (THE TICKET SHEET)
  Cause: opening a ticket made the sheet visible with an inline display:block.
  On a phone app.css turns that sheet into a flex column so its INNER body can
  do the scrolling, and the inline block beat that flex rule. The inner body
  then grew to its whole content height and had nothing left to scroll, while
  the sheet clipped everything past the first screen. The page behind was
  locked as well (the sheet sets body overflow:hidden), so the ticket could not
  be scrolled AT ALL - not merely past the last section.

  Measured on the old code: body content 1490px, visible 1490px, scroll travel
  0px. That is the "cannot scroll" you saw.

  Fixed in two places:
    - app.js now asks for the layout the current width needs - a flex column on
      a phone, the plain scrolling panel on desktop - instead of always block.
    - tickets.php adds a small CSS backstop that keeps the sheet a flex column
      at phone width even if the browser is still running an older cached
      app.js. Closing is unaffected: the backstop only matches the inline
      "display: block" the old code wrote, never "display: none".

  Result - scroll travel now: 852px at 549px wide, 907px at 390px, 1129px at
  320px, with the last section of the ticket reachable in every case.

  Upload BOTH files for this one, then reload the page on the phone:
    public/assets/js/app.js       REQUIRED
    public/pages/tickets.php      REQUIRED (this one alone is enough to get
                                            scrolling back thanks to the CSS
                                            backstop; it also removes the old
                                            sheet rule that let the sheet run
                                            under the tab bar)
  app.js is requested with a ?v=<modified-time> query, so a fresh upload gets a
  new URL and the browser picks it up without a manual cache clear.


V5 - THE STATISTICS PAGE
  The page carried two competing stylesheets (an old six-column KPI grid plus a
  later grouped-summary block that overrode parts of it), which is why cards,
  type sizes and spacing looked inconsistent. Both are gone, replaced by one
  block, along with the visual problems you could see in the screenshot:

    - Daily Workload now has a labelled value axis (10 / 5 / 0) and faint
      gridlines, so a quiet period reads as "low activity" instead of "broken
      chart". It also states the busiest day and the daily average, and says so
      plainly when the period has no sessions at all.
    - Days with nothing are no longer drawn as 2px stubs that look like data.
    - Axis labels, day labels, table headings and captions went from 8-10px to
      9.5-11.5px with darker grey, so they can actually be read.
    - On a phone the 30-day plot no longer squeezes two bars into about 2px
      each: it keeps a readable column width and scrolls, opening on the most
      recent days.
    - Long issue titles are cleaned up. The multi-line text stored on the
      session is collapsed into one readable sentence, clamped to two lines,
      with the full text still available on hover/tap. No more "...of p...".
    - "Most Common Problems" now says what its bar means (volume) and shows the
      solved rate as a colour-coded badge - green at 70%+, amber 40-69%, red
      below - instead of a small grey "33% solved" next to a volume bar.
    - Work Status pairs the count with its share, colour-codes every status and
      ends with the total.
    - "Unspecified device" no longer carries a pointless "Other" chip.
    - Dark mode goes through CSS variables, so nothing is left bright or
      unreadable when you switch themes.

  Only one app file changed: public/pages/admin/statistics.php. No database or
  SQL change - every number still comes from the queries that were already there
  (the chart ceiling is now rounded up so the axis labels land on whole numbers).


V4 - "I CAN'T VIEW ALL TICKETS" + THE VIEW-TICKET SHEET WAS UNUSABLE ON A PHONE
  Two separate problems, both fixed.

  1. TICKETS THAT COULD NOT BE VIEWED AT ALL
     The status row only had New / In Progress / Solved / Escalated. The
     database also stores partial, unsolved and cancelled tickets, so those
     had no button that could ever show them, and "Solved" counted partial
     repairs while the button filtered on solved only. Now:
       - a new "All (n)" button shows the whole list;
       - "Partial (n)" and "Unsolved / Cancelled (n)" appear when such tickets
         exist, so every ticket has a way in;
       - the counts are per status again (New no longer counts partials);
       - Clear filters lands on All instead of snapping back to New;
       - if the queue holds none of the usual statuses the page opens on All
         rather than showing a convincing "no tickets" card grid.
     Also, managers/admins/supervisors now get a Mine / All tickets switch on
     the Tickets page; it opens on All tickets so the team queue is visible.
     If you are a technician, All tickets shows your own tickets only - that is
     by design, not a bug.

  2. THE "VIEW TICKET" SHEET ON A PHONE
     The detail sheet is now a real phone sheet: it sits between the top bar and
     the tab bar, it scrolls internally (so the close button and the tab bar stay
     reachable), and nothing overflows the screen any more.
       - Long addresses, serials and report values wrap instead of widening the
         sheet past the right edge.
       - Report rows (Action Taken, Result of Checking, ...) stack the label
         above the value on a phone. They used to keep a 150px label column and
         letter-break the label ("Acti / on Tak / en").
       - The blue sheet header now uses white text: the title, the ticket
         number / serial line and the "Assigned to" line were dark grey on the
         blue gradient and impossible to read. The serial is clipped on that one
         line on purpose - the full value is listed under Device.
       - Opening a ticket scrolls the sheet back to the top instead of leaving
         you half-way down the previous one.
       - The card/table switch is hidden on phones and cards are always used:
         the table is 980px wide and could not be read on a phone.

  3. THE EMPTY "Map is unavailable right now." BOX
     When the map cannot be drawn (no tiles, no internet, address not found) the
     sheet used to show a 190px grey box saying nothing and offering nothing.
     It now shows the saved address with "Open in Maps" and "Copy address"
     buttons, so you can still navigate to the site.


V3 - "FIX STILL ERROR ... 404" ON THE LIVE SITE
  V2's upload list was too short. tickets.php pulls in two files that were not
  in it, so on a server that never had them the browser asked for them and the
  host answered 404 - one console line per file, with no hint about which file.
  V3 fixes that three ways:

  1. The upload list above is now complete (workspace-refresh.css and
     ticket-workspace.js are REQUIRED), and both files are inside this folder.
  2. tickets.php only prints a <link>/<script> tag when the file is actually
     present on the server, and prints an explicit console.error naming the
     missing file when it is not. A missing file can no longer be a silent 404.
  3. Two other places used a path that only exists on your XAMPP machine
     (/fielditservice/...). On InfinityFree the app is at the domain root, so
     those paths 404 there:
       - public/pages/.htaccess  pointed its 403 error page at
         /fielditservice/public/index.php. Replaced with a relative rewrite to
         the front controller, which is correct for a domain root, a /public
         URL, and a sub-folder install alike.
       - api/upload.php returned "/fielditservice/public/uploads/..." as the URL
         of an uploaded picture. It now builds that URL from the request, so it
         is correct on any host.

  Find the exact URL of any remaining 404 without guessing: open the browser
  console (F12) and type

      fielditMissingResources

  It lists every first-party file the browser failed to fetch on this page.
  app.js now also logs "[Field IT] missing ... - the server returned 404 for:
  <url>" the moment it happens, and names the API endpoint for a 404 from the
  api() helper. Paste that line and the file to upload is obvious.

  Separately: opening a ticket used to fire extra 404s for Leaflet's default
  map pin (assets/lib/images/marker-icon.png), which the bundled Leaflet build
  never shipped. The ticket map now uses the same inline SVG pin as the New
  Ticket map, so no marker image is requested at all.


V2 - "IT SAID SUCCESSFUL BUT I DON'T SEE THE TICKET"
  The scan only FILLS the form; it never saves anything. The old confirmation
  was a green success toast, so it read as "ticket created" when in fact the
  ticket is only saved on step 2 ("Create Ticket"). Now:
    - The scan confirmation is an info message that says nothing is saved yet
      and that you still have to tap Next and Create Ticket.
    - The created-ticket toast no longer says "time in at " with an empty value
      (a new ticket has no Time In until "Start Time In" is pressed).
    - "My Tickets" no longer shows "No tickets yet" when the ticket query itself
      fails - it says the list could not be loaded and offers Retry (the real
      error goes to the PHP error log).
    - A session that expires mid-form reports "Your session expired" instead of
      an unrelated JavaScript error.
  NOTE: My Tickets only lists tickets whose owner is you. A ticket created by
  another account/device will not appear on yours - managers see the whole team
  queue on Ticket Management (/admin/ticket-approvals).


V1 - TICKET SCAN
  "New Ticket" can be filled by photographing the printed Work Order
  (camera -> OCR -> review -> fill the form). Manual entry still works for every
  field the scan cannot read.

  HOW IT WORKS ON THE FORM
    New Ticket -> "Scan Work Order" (top of step 1) -> camera opens -> Capture
    -> "Read work order" -> review panel (every value editable) -> "Fill the
    ticket form".

  FIELD MAPPING
    Work Order No. (SD1040842)          -> Ticket No. (1040842, SD prefix kept)
    Company Name                        -> Company (existing entry reused, else
                                           the "Other" box - the pending
                                           approval flow is unchanged)
    Address                             -> Address (also geocoded, so travel/ETA
                                           works)
    Task Description / Reported Problem -> Task (first line) + Problem (full text)
    Unit Reported                       -> Device: matched against the equipment
                                           table by serial/model first, else the
                                           device type is guessed from the model
                                           family and the printed model goes into
                                           the model box
    Serial/CRTL No.                     -> Serial Number (O/0 and I/1 OCR slips
                                           are forgiven when matching equipment,
                                           and the serial on the equipment record
                                           wins)
    Contact Person                      -> Customer Name
    Request / Contact Nos.              -> Note (reference line)

  LIMITS
    - The camera needs HTTPS (or http://localhost in XAMPP). Your live site is
      already HTTPS.
    - The FIRST scan downloads the OCR engine (about 9 MB, one time, cached by
      the browser afterwards). Offline, the scanner says so and the form can
      still be typed by hand.
    - Printed text reads well. Handwriting is not recognised - those fields are
      shown as "not read - fill in" and left blank for manual entry.


FILES CHANGED IN V6
  public/assets/js/app.js             openTicketDrawer() asks for the sheet
                                      layout the width needs (flex column on a
                                      phone) instead of a plain display:block,
                                      which is what killed the inner scroller
  public/pages/tickets.php            CSS backstop keeping the phone sheet a
                                      flex column even with a cached app.js;
                                      the sheet body also gets
                                      overscroll-behavior:contain and
                                      -webkit-overflow-scrolling:touch
  tests/ticket-drawer-mobile.php      now opens the sheet exactly the way app.js
                                      does (the display value is read out of
                                      app.js rather than copied) and adds 4
                                      checks: app.js asks for flex, the sheet
                                      renders as a flex column, the whole
                                      ticket is reachable (travel > 0px), and
                                      the backstop works with an old app.js.
                                      It also skips itself outside phone width
                                      instead of reporting false failures.

FILES CHANGED IN V5
  public/pages/admin/statistics.php   the whole page skin: one style block, a
                                      chart with an axis, gridlines and a real
                                      empty state, readable rank rows with
                                      solved-rate badges, colour-coded work
                                      status, and dark-mode variables
  tests/statistics-layout.php         NEW: 41 checks on the rendered statistics
                                      page from 320px to 1440px wide, light and
                                      dark, re-run on resize
  tests/statistics-labels.php         NEW: 27 checks on the label tidy-up, the
                                      solved-rate bands and the chart ceiling
                                      (run it with: php tests/statistics-labels.php)

FILES CHANGED IN V4
  public/pages/tickets.php          All / Partial / Unsolved-Cancelled filter
                                    buttons; Mine / All tickets switch; phone
                                    layout for the View Ticket sheet; card view
                                    forced on phones
  public/assets/js/app.js           filter matching for all/other, Clear filters
                                    and the default filter land on All; the
                                    selected chip is scrolled into view on a
                                    phone; the sheet opens at the top; the map
                                    fallback (address + Maps link) instead of a
                                    dead grey box
  tests/ticket-filter-status.html   NEW: 12 checks that every stored status is
                                    reachable from the filter row
  tests/ticket-drawer-mobile.php    NEW: 20 checks on the phone layout of the
                                    View Ticket sheet at 320-430px wide (also
                                    covers the Mine / All tickets switch)

FILES CHANGED IN V3
  public/pages/tickets.php          asset tags are emitted only when the file
                                    exists, with a loud console.error otherwise
  public/assets/js/app.js           ticket map uses the inline SVG pin (no more
                                    missing-marker 404s); adds the missing-file
                                    reporter + fielditMissingResources; api()
                                    names the endpoint behind a 404
  public/assets/css/workspace-refresh.css   now part of the required upload set
  public/assets/js/ticket-workspace.js      now part of the required upload set
  public/pages/.htaccess            host-portable page guard (was
                                    /fielditservice/public/index.php)
  api/upload.php                    upload URL built from the request (was
                                    /fielditservice/public/uploads/...), plus the
                                    type/extension are now whitelisted


HOW TO RE-VERIFY AFTER FUTURE EDITS (no test runner, no Node needed)
  Open these two files in a browser; every line of output must start with PASS
    tests/ticket-scan-parse.html   (16 checks: work-order text parsing)
    tests/ticket-scan-apply.html   (22 checks: values written to the form)
  Or headless from the project root:
    "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe" --headless=new --disable-gpu --dump-dom tests/ticket-scan-parse.html

  For the layout checks, serve the project root over PHP and open the page in a
  browser, because they measure a real rendered layout. Narrow the window (or use
  the device toolbar) and the checks re-run for that width - every line must
  start with PASS:
    php -S 127.0.0.1:8123 -t .
    http://127.0.0.1:8123/tests/ticket-filter-status.html   (12 checks)
    http://127.0.0.1:8123/tests/ticket-drawer-mobile.php    (24 checks; phone
                                                             width only - it
                                                             says so above 767px
                                                             instead of failing)
    http://127.0.0.1:8123/tests/statistics-layout.php       (41 checks at any
                                                             width from 320px up)
  Pure-logic checks need no browser:
    php tests/statistics-labels.php                         (27 checks)
