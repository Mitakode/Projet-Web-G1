# Projet-Web-G1

**Context**
Students search for internships through their networks (LinkedIn, classmates, etc.) and job offers. This project aims to
create a website that aggregates internship offers and stores information about companies that have already hosted an intern
or are currently looking for one.

**Goals**

- Centralize internship offers
- Make it easier to guide students based on skills
- Provide interfaces adapted to each profile (admin, pilot, student)
- Follow development, SEO, and security best practices

**User profiles**

- Administrator
- Program pilot
- Student

**Expected features**
Access management

- SFx1 Authentication and access management

Company management

- SFx2 Search and view a company
- SFx3 Create a company
- SFx4 Edit a company
- SFx5 Rate a company
- SFx6 Delete a company

Internship offer management

- SFx7 Search and view an offer
- SFx8 Create an offer
- SFx9 Edit an offer
- SFx10 Delete an offer
- SFx11 View offer statistics

Pilot account management

- SFx12 Search and view a pilot account
- SFx13 Create a pilot account
- SFx14 Edit a pilot account
- SFx15 Delete a pilot account

Student account management

- SFx16 Search and view a student account
- SFx17 Create a student account
- SFx18 Edit a student account
- SFx19 Delete a student account

Application management

- SFx20 Apply to an offer (CV + cover letter)
- SFx21 View offers the student has applied to
- SFx22 View offers applied to by the pilot's students

Wishlist management

- SFx23 View offers added to the wishlist
- SFx24 Add an offer to the wishlist
- SFx25 Remove an offer from the wishlist

Cross-cutting features

- SFx27 Pagination for lists
- SFx28 Legal notices

Bonus

- Mobile access via PWA

**Technical specifications**
Architecture and stack

- MVC architecture required
- Apache server
- Frontend: HTML5, CSS3, JS
- Backend: PHP (OOP required)
- SQL database (MySQL, PostgreSQL, MariaDB, ...)
- Backend-side template engine

Quality constraints

- Semantic and W3C-valid HTML
- Structured and consistent CSS
- PSR-12 conventions appreciated
- Field validation on both front-end (HTML/JS) and back-end (PHP)

Restrictions and allowances

- CMS forbidden (WordPress, Drupal, Joomla, etc.)
- Frameworks forbidden (React, Angular, Vue, Laravel, Symfony)
- LESS/Sass and jQuery allowed

Security

- Secure cookies for login information
- No sensitive data stored in plain text
- Protection against SQLi, XSS, CSRF
- HTTPS

SEO

- `title`, `meta description`, headings (Hn), `alt` attributes
- Keywords in metadata
- Load time < 3 s
- Readable and consistent URLs
- `sitemap.xml` and `robots.txt`

Other requirements

- Separate vhost for static assets
- Responsive design with burger menu on small screens
- Backend URL routing
- PHPUnit unit tests for at least one controller
- DB relations with foreign keys

**Project phases**

- Phase 1 Project kickoff (Scrum, roles, backlog, sprints, daily)
- Phase 2 Mockups then frontend (wireframe, navigation, mobile first)
- Phase 3 Backend development (start)
- Phase 4 Database modeling and setup
- Phase 5 Backend development (auth, DB, tests)
- Phase 6 Finalization (JavaScript additions)

**Deliverable and defense**

- Short presentation (about 5 minutes)
- Technical demo
- Individual Q&A

**Team size**

- Project sized for 4 students
