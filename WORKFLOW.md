# NESSFI Backend - Complete Workflow

## System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                         NESSFI SURVEY SYSTEM                     │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┐      ┌──────────────┐      ┌──────────────┐
│   Frontend   │◄────►│   Laravel    │◄────►│   Database   │
│  (React/Vue) │      │   Backend    │      │    (MySQL)   │
└──────────────┘      └──────────────┘      └──────────────┘
```

---

## 1. User Registration & Authentication Flow

### 1.1 User Registration
```
┌─────────┐
│  User   │
└────┬────┘
     │
     │ POST /api/user-register
     │ {name, email, password, password_confirmation}
     ▼
┌─────────────────────────────────────┐
│  AuthController::register()         │
│  - Validate input                   │
│  - Create user (email_verified=0)   │
│  - Generate 6-digit OTP             │
│  - Send verification email          │
└────┬────────────────────────────────┘
     │
     │ Response: {user, message}
     ▼
┌─────────────────────────────────────┐
│  User receives email with OTP       │
└─────────────────────────────────────┘
```

### 1.2 Email Verification
```
┌─────────┐
│  User   │
└────┬────┘
     │
     │ POST /api/verify-email
     │ {email, otp}
     ▼
┌─────────────────────────────────────┐
│  AuthController::verifyEmail()      │
│  - Check OTP validity               │
│  - Mark email_verified = 1          │
│  - Create Sanctum token             │
└────┬────────────────────────────────┘
     │
     │ Response: {user, token, message}
     ▼
┌─────────────────────────────────────┐
│  User is now verified & logged in   │
└─────────────────────────────────────┘
```

### 1.3 Resend Verification OTP
```
┌─────────┐
│  User   │
└────┬────┘
     │
     │ POST /api/resend-verification
     │ {email}
     ▼
┌─────────────────────────────────────┐
│  AuthController::resendVerification │
│  - Generate new OTP                 │
│  - Send new email                   │
└────┬────────────────────────────────┘
     │
     │ Response: {message}
     ▼
┌─────────────────────────────────────┐
│  New OTP sent to user email         │
└─────────────────────────────────────┘
```

### 1.4 User Login
```
┌─────────┐
│  User   │
└────┬────┘
     │
     │ POST /api/login
     │ {email, password}
     ▼
┌─────────────────────────────────────┐
│  AuthController::login()            │
│  - Validate credentials             │
│  - Check email verification         │
│  - Create Sanctum token             │
└────┬────────────────────────────────┘
     │
     │ Response: {user, token, message}
     ▼
┌─────────────────────────────────────┐
│  User logged in with token          │
└─────────────────────────────────────┘
```

---

## 2. Survey Structure Flow

### 2.1 Database Structure
```
┌──────────────┐
│   Sections   │ (Seeded - Read Only)
│  - id        │
│  - section   │
└──────┬───────┘
       │ 1
       │
       │ N
┌──────▼───────────────────┐
│      Questions           │ (Admin CRUD)
│  - id                    │
│  - section_id            │
│  - question              │
│  - question_type         │
│  - text_response         │
└──────┬───────────────────┘
       │ 1
       │
       │ N
┌──────▼───────────────────┐
│       Options            │ (Admin CRUD)
│  - id                    │
│  - question_id           │
│  - option_text           │
└──────────────────────────┘

┌──────────────────────────┐
│      Responses           │ (User CRUD)
│  - id                    │
│  - user_id               │
│  - question_id           │
│  - selected_option_id    │
│  - text_response         │
└──────────────────────────┘
```

### 2.2 Sections (Pre-populated via Seeder)
```
Section 1: Basic Questions (10 questions)
  ├─ Demographics
  ├─ Personal info
  └─ Background

Section 2: Self Feedback (10 questions)
  ├─ Job satisfaction
  ├─ Skills assessment
  └─ Personal development

Section 3: Office Feedback (10 questions)
  ├─ Work environment
  ├─ Team collaboration
  └─ Company culture
```

---

## 3. Admin Workflow (Questions & Options Management)

### 3.1 View All Questions
```
┌─────────┐
│  Admin  │ (Authenticated)
└────┬────┘
     │
     │ GET /api/questions
     │ Headers: {Authorization: Bearer <token>}
     ▼
┌─────────────────────────────────────┐
│  QuestionController::index()        │
│  - Fetch all questions with         │
│    sections and options             │
└────┬────────────────────────────────┘
     │
     │ Response: {data: [questions], message}
     ▼
┌─────────────────────────────────────┐
│  List of all questions displayed    │
└─────────────────────────────────────┘
```

### 3.2 Create New Question
```
┌─────────┐
│  Admin  │
└────┬────┘
     │
     │ POST /api/questions
     │ {
     │   section_id,
     │   question,
     │   question_type,
     │   text_response,
     │   options: [{option_text}, ...]
     │ }
     ▼
┌─────────────────────────────────────┐
│  QuestionController::store()        │
│  - Validate input                   │
│  - Create question                  │
│  - Create associated options        │
└────┬────────────────────────────────┘
     │
     │ Response: {data: question, message}
     ▼
┌─────────────────────────────────────┐
│  New question created successfully  │
└─────────────────────────────────────┘
```

### 3.3 Update Question
```
┌─────────┐
│  Admin  │
└────┬────┘
     │
     │ PUT /api/questions/{id}
     │ {
     │   question,
     │   question_type,
     │   text_response,
     │   options: [{id?, option_text}, ...]
     │ }
     ▼
┌─────────────────────────────────────┐
│  QuestionController::update()       │
│  - Update question details          │
│  - Sync options (add/update/delete) │
└────┬────────────────────────────────┘
     │
     │ Response: {data: question, message}
     ▼
┌─────────────────────────────────────┐
│  Question updated successfully      │
└─────────────────────────────────────┘
```

### 3.4 Delete Question
```
┌─────────┐
│  Admin  │
└────┬────┘
     │
     │ DELETE /api/questions/{id}
     ▼
┌─────────────────────────────────────┐
│  QuestionController::destroy()      │
│  - Delete question                  │
│  - Cascade delete options           │
└────┬────────────────────────────────┘
     │
     │ Response: {message}
     ▼
┌─────────────────────────────────────┐
│  Question deleted successfully      │
└─────────────────────────────────────┘
```

---

## 4. User Survey Response Flow

### 4.1 View Sections & Questions
```
┌─────────┐
│  User   │ (Authenticated)
└────┬────┘
     │
     │ GET /api/sections
     │ Headers: {Authorization: Bearer <token>}
     ▼
┌─────────────────────────────────────┐
│  SectionController::index()         │
│  - Fetch all sections with          │
│    questions (without options)      │
└────┬────────────────────────────────┘
     │
     │ Response: {data: [sections], message}
     ▼
┌─────────────────────────────────────┐
│  Display sections list              │
└─────────────────────────────────────┘
```

### 4.2 View Section Details with Questions
```
┌─────────┐
│  User   │
└────┬────┘
     │
     │ GET /api/sections/{id}
     ▼
┌─────────────────────────────────────┐
│  SectionController::show()          │
│  - Fetch section with questions     │
│    and all options                  │
└────┬────────────────────────────────┘
     │
     │ Response: {data: section, message}
     ▼
┌─────────────────────────────────────┐
│  Display questions with options     │
│  for user to answer                 │
└─────────────────────────────────────┘
```

### 4.3 Submit Response
```
┌─────────┐
│  User   │
└────┬────┘
     │
     │ POST /api/responses
     │ {
     │   question_id,
     │   selected_option_id (optional),
     │   text_response (optional)
     │ }
     ▼
┌─────────────────────────────────────┐
│  ResponseController::store()        │
│  - Validate input                   │
│  - Check question type requirements │
│  - Create/Update response           │
└────┬────────────────────────────────┘
     │
     │ Response: {data: response, message}
     ▼
┌─────────────────────────────────────┐
│  Response saved successfully        │
└─────────────────────────────────────┘
```

### 4.4 View User's Responses
```
┌─────────┐
│  User   │
└────┬────┘
     │
     │ GET /api/responses
     ▼
┌─────────────────────────────────────┐
│  ResponseController::index()        │
│  - Fetch all responses for          │
│    authenticated user               │
└────┬────────────────────────────────┘
     │
     │ Response: {data: [responses], message}
     ▼
┌─────────────────────────────────────┐
│  Display user's survey responses    │
└─────────────────────────────────────┘
```

### 4.5 Delete Response
```
┌─────────┐
│  User   │
└────┬────┘
     │
     │ DELETE /api/responses/{id}
     ▼
┌─────────────────────────────────────┐
│  ResponseController::destroy()      │
│  - Verify ownership                 │
│  - Delete response                  │
└────┬────────────────────────────────┘
     │
     │ Response: {message}
     ▼
┌─────────────────────────────────────┐
│  Response deleted successfully      │
└─────────────────────────────────────┘
```

---

## 5. Question Types & Response Rules

### Question Types
```
1. multiple_choice_single
   - User selects ONE option
   - selected_option_id: required
   - text_response: based on text_response field

2. checkbox_multiple
   - User selects MULTIPLE options
   - Multiple response records created
   - selected_option_id: required (per selection)
   - text_response: based on text_response field

3. text_only
   - User provides text answer
   - selected_option_id: null
   - text_response: required

4. mcq_textarea
   - User selects ONE option + text
   - selected_option_id: required
   - text_response: based on text_response field

5. checkbox_textarea
   - User selects MULTIPLE options + text
   - selected_option_id: required (per selection)
   - text_response: based on text_response field
```

### Text Response Rules
```
- no_text: text_response must be null
- optional: text_response can be provided or null
- required: text_response must be provided
```

---

## 6. Complete User Journey Example

```
Step 1: Registration
  POST /api/user-register
  → User created, OTP sent

Step 2: Email Verification
  POST /api/verify-email
  → User verified, token received

Step 3: View Sections
  GET /api/sections
  → See: Basic Questions, Self Feedback, Office Feedback

Step 4: View Section 1 Details
  GET /api/sections/1
  → See all 10 questions with options

Step 5: Answer Question 1 (Text)
  POST /api/responses
  {question_id: 1, text_response: "25"}

Step 6: Answer Question 2 (MCQ)
  POST /api/responses
  {question_id: 2, selected_option_id: 5}

Step 7: Continue through all sections...

Step 8: View My Responses
  GET /api/responses
  → See all submitted answers

Step 9: Logout
  POST /api/logout
  → Token revoked
```

---

## 7. API Endpoints Summary

### Public Routes
```
POST   /api/user-register          - Register new user
POST   /api/login                  - Login user
POST   /api/verify-email           - Verify email with OTP
POST   /api/resend-verification    - Resend OTP
```

### Protected Routes (Require Authentication)
```
GET    /api/user                   - Get current user
POST   /api/logout                 - Logout user

# Sections (Read Only)
GET    /api/sections               - List all sections
GET    /api/sections/{id}          - View section details

# Questions (Admin CRUD)
GET    /api/questions              - List all questions
POST   /api/questions              - Create question
GET    /api/questions/{id}         - View question
PUT    /api/questions/{id}         - Update question
DELETE /api/questions/{id}         - Delete question

# Responses (User CRUD)
GET    /api/responses              - List user's responses
POST   /api/responses              - Submit response
GET    /api/responses/{id}         - View response
DELETE /api/responses/{id}         - Delete response
```

---

## 8. Data Seeding

### Initial Setup
```
php artisan migrate:fresh
php artisan db:seed --class=AdminSeeder
php artisan db:seed --class=SectionSeeder
```

### Seeded Data
```
Admin User:
  - Email: admin@admin.com
  - Password: password
  - Email Verified: Yes

Sections (3):
  1. Basic Questions (10 questions)
  2. Self Feedback (10 questions)
  3. Office Feedback (10 questions)

Total: 30 questions with options
```

---

## 9. Security & Validation

### Authentication
- Laravel Sanctum for API token management
- Email verification required before login
- OTP-based verification (6 digits)

### Authorization
- Users can only view/delete their own responses
- Admin can manage all questions
- Sections are read-only (seeded data)

### Validation
- All requests validated via Form Requests
- Question type validation
- Option requirements based on question type
- Text response requirements based on text_response field

---

## 10. Error Handling

### Common Error Responses
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Validation error"]
  }
}
```

### HTTP Status Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 404: Not Found
- 422: Validation Error
- 500: Server Error
