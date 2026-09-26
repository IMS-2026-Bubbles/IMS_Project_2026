# Terms of Service Documentation

## Overview

This document outlines the creation and structure of Scriba's Terms of Service (ToS) document. The ToS is a legal agreement between Scriba and users of the application, covering usage rights, responsibilities, data handling, and GDPR compliance.

## Document Structure

The Terms of Service is divided into the following sections:

1. **Acceptance of Terms** — Introduction and agreement mechanism
2. **About Scriba** — Service description and operator information
3. **User Eligibility and Accounts** — Age requirements, account creation, and user responsibilities
4. **Acceptable Use** — Prohibited conduct and acceptable use policies
5. **Intellectual Property Rights** — Ownership of content and data
6. **Data Privacy and GDPR Compliance** — Personal data handling, user rights, and compliance
7. **Data Security** — Security measures and limitations
8. **Data Retention and Deletion** — Retention policies and user rights
9. **Limitation of Liability** — Legal disclaimers and liability caps
10. **Disclaimer of Warranties** — As-is service provision
11. **Indemnification** — User indemnification of Scriba
12. **Service Availability and Modifications** — Uptime disclaimers and change rights
13. **Termination** — Account and service termination
14. **Governing Law and Dispute Resolution** — Jurisdiction and dispute handling
15. **Changes to Terms** — Terms modification policy
16. **Contact Information** — Support and legal inquiry contacts

## Key GDPR Compliance Elements

The document incorporates:

- **Legal basis for processing** — Contractual necessity and legitimate interests
- **Data subject rights** — Right of access, rectification, erasure, restriction, portability, objection, and automated decision-making
- **Data Processing Agreement (DPA)** — Reference to DPA for users processing personal data of research subjects
- **Data retention policy** — 90 days for login logs; experiment data retained for account duration
- **Data controller/processor roles** — Clarification that Scriba is primarily a data controller; users may be controllers for their experiment data
- **International transfers** — Statement that processing may occur in different jurisdictions
- **Data breach notification** — Commitment to notify users of breaches where legally required

## Sources and Templates Used

### Primary Templates

- **GDPR-compliant Template**: [Termly Terms and Conditions Generator](https://termly.io/products/terms-and-conditions/) — Covers GDPR compliance, data privacy, and liability
- **Open Source Template**: [GitHub Creative Commons T&C Template](https://github.com/mozilla/legal-docs) — Mozilla's legal templates (reference for structure)
- **GDPR-specific guidance**: [EDPB Guidelines](https://edpb.ec.europa.eu/) — European Data Protection Board guidelines on transparency, user rights, and processing

### Reference Materials

- **GDPR Text**: [Regulation (EU) 2016/679](https://eur-lex.europa.eu/eli/reg/2016/679/oj) — Official GDPR regulation
- **GDPR Compliance Checklist**: [GDPR.eu](https://gdpr.eu/) — Practical GDPR compliance guidance
- **Research Data Handling**: [FAIR Data Principles](https://www.go-fair.org/fair-principles/) — Best practices for research data
- **Data Protection Authority**: Relevant to your institution's jurisdiction (e.g., [CNIL for France](https://www.cnil.fr/), [ICO for UK](https://ico.org.uk/))

## Customization Notes for Scriba

This template is tailored for Scriba with the following specific adaptations:

- **Research-focused**: Acknowledges that users handle research data and may process personal data of research subjects
- **University/Corporate context**: Suitable for university researchers and corporate R&D teams
- **Pseudo-anonymization requirement**: Notes that users must handle sensitive personal data according to institutional policy
- **Planned features**: Includes placeholder language for encryption and other security enhancements
- **90-day log retention**: Specific to your planned data retention policy
- **Account deletion**: Includes user rights to request account deletion
- **HTTPS requirement**: Specifies that communication is encrypted in transit

## How to Use This Document

1. **Review and Customize**: Read through `terms_of_service.md` and customize sections marked `[CUSTOMIZE]` with your specific information (support email, legal contact, specific data practices, etc.)

2. **Legal Review**: Have a lawyer or legal counsel in your jurisdiction review before publishing. GDPR compliance varies by jurisdiction, and local data protection authorities may have additional requirements.

3. **Obtain Consent**: Implement a checkbox or acceptance mechanism requiring users to affirmatively accept the ToS during registration or login.

4. **Make Publicly Available**: Publish the ToS on your website or within the application and maintain a version history.

5. **Convert to PDF**: Use a markdown-to-PDF converter (Quarto, Pandoc, or your preferred tool) to generate a PDF version for distribution.

6. **Keep Records**: Maintain records of when users accepted the ToS for compliance purposes.

## Potential Future Additions

As Scriba develops, consider updating the ToS to cover:

- **Data Processing Agreement (DPA)** — If you process data on behalf of academic institutions
- **Sub-processors** — If you use cloud hosting or other third-party services
- **Automated decision-making** — If experiment analysis includes algorithmic recommendations
- **Cookies and Tracking** — If you implement analytics
- **Export/Backup policies** — If you provide data export functionality
- **Collaboration features** — If you implement multi-user experiment access

## Version History

- **v1.0** — September 26, 2026 — Initial GDPR-compliant draft
