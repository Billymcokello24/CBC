// CBC Learning Platform Types

import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

// ============================================================================
// User & Authentication Types
// ============================================================================
export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    phone?: string;
    status: 'active' | 'inactive' | 'suspended';
    email_verified_at?: string;
    roles: Role[];
    permissions: string[];
    school?: School;
    created_at: string;
    updated_at: string;
}

export interface Role {
    id: number;
    name: string;
    guard_name: string;
    permissions?: Permission[];
}

export interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

// ============================================================================
// School Types
// ============================================================================
export interface School {
    id: number;
    name: string;
    code: string;
    registration_number?: string;
    school_type?: SchoolType;
    school_level?: SchoolLevel;
    logo?: string;
    motto?: string;
    vision?: string;
    mission?: string;
    email?: string;
    phone?: string;
    website?: string;
    address?: string;
    county?: string;
    sub_county?: string;
    gender_type: 'boys' | 'girls' | 'mixed';
    boarding_type: 'day' | 'boarding' | 'mixed';
    status: 'active' | 'inactive' | 'suspended';
    created_at: string;
    updated_at: string;
}

export interface SchoolType {
    id: number;
    name: string;
    code: string;
    description?: string;
}

export interface SchoolLevel {
    id: number;
    name: string;
    code: string;
    description?: string;
}

// ============================================================================
// Student Types
// ============================================================================
export interface Student {
    id: number;
    admission_number: string;
    upi?: string;
    first_name: string;
    middle_name?: string;
    last_name: string;
    full_name: string;
    gender: 'male' | 'female' | 'other';
    date_of_birth: string;
    age?: number;
    nationality?: string;
    religion?: string;
    home_address?: string;
    county?: string;
    sub_county?: string;
    photo?: string;
    admission_date: string;
    blood_group?: string;
    has_special_needs: boolean;
    boarding_status: 'day' | 'boarding';
    status: 'active' | 'inactive' | 'transferred' | 'graduated' | 'expelled' | 'dropped_out';
    school?: School;
    current_class?: SchoolClass;
    guardians?: Guardian[];
    created_at: string;
    updated_at: string;
}

// ============================================================================
// Guardian/Parent Types
// ============================================================================
export interface Guardian {
    id: number;
    first_name: string;
    middle_name?: string;
    last_name: string;
    full_name: string;
    id_number?: string;
    gender?: 'male' | 'female' | 'other';
    email?: string;
    phone: string;
    alternate_phone?: string;
    occupation?: string;
    employer?: string;
    home_address?: string;
    county?: string;
    relationship_type?: 'father' | 'mother' | 'guardian' | 'other';
    photo?: string;
    is_emergency_contact: boolean;
    can_pickup: boolean;
    receives_communication: boolean;
    is_active: boolean;
    students?: Student[];
    created_at: string;
    updated_at: string;
}

// ============================================================================
// Teacher Types
// ============================================================================
export interface Teacher {
    id: number;
    staff_number: string;
    tsc_number?: string;
    first_name: string;
    middle_name?: string;
    last_name: string;
    full_name: string;
    gender: 'male' | 'female' | 'other';
    date_of_birth?: string;
    nationality?: string;
    email?: string;
    phone: string;
    alternate_phone?: string;
    address?: string;
    county?: string;
    photo?: string;
    date_joined: string;
    contract_type?: string;
    employment_type?: 'full_time' | 'part_time';
    status: 'active' | 'inactive' | 'on_leave' | 'terminated' | 'resigned' | 'retired';
    school?: School;
    department?: Department;
    qualifications?: TeacherQualification[];
    subject_assignments?: SubjectAssignment[];
    created_at: string;
    updated_at: string;
}

export interface TeacherQualification {
    id: number;
    qualification_type: string;
    institution: string;
    specialization?: string;
    year_obtained: number;
    certificate_file?: string;
}

export interface SubjectAssignment {
    id: number;
    teacher_id: number;
    subject_id: number;
    class_id: number;
    subject?: Subject;
    class?: SchoolClass;
}

// ============================================================================
// Academic Structure Types
// ============================================================================
export interface AcademicYear {
    id: number;
    name: string;
    start_date: string;
    end_date: string;
    is_current: boolean;
    status: 'active' | 'completed' | 'upcoming';
}

export interface AcademicTerm {
    id: number;
    name: string;
    academic_year_id: number;
    term_number: number;
    start_date: string;
    end_date: string;
    is_current: boolean;
    status: 'active' | 'completed' | 'upcoming';
    academic_year?: AcademicYear;
}

export interface GradeLevel {
    id: number;
    name: string;
    code: string;
    level_order: number;
    description?: string;
}

export interface Stream {
    id: number;
    name: string;
    code: string;
}

export interface SchoolClass {
    id: number;
    name: string;
    code: string;
    grade_level_id: number;
    stream_id?: number;
    class_teacher_id?: number;
    capacity?: number;
    grade_level?: GradeLevel;
    stream?: Stream;
    class_teacher?: Teacher;
    students_count?: number;
}

export interface Department {
    id: number;
    name: string;
    code: string;
    description?: string;
    head_id?: number;
    head?: Teacher;
}

// ============================================================================
// Curriculum Types
// ============================================================================
export interface LearningArea {
    id: number;
    name: string;
    code: string;
    category: string;
    description?: string;
    display_order: number;
    subjects?: Subject[];
}

export interface Subject {
    id: number;
    name: string;
    code: string;
    learning_area_id: number;
    subject_type: 'core' | 'optional' | 'elective';
    is_examinable: boolean;
    description?: string;
    learning_area?: LearningArea;
}

export interface Competency {
    id: number;
    name: string;
    code: string;
    category: 'core' | 'subject_specific';
    description?: string;
    display_order: number;
}

export interface Strand {
    id: number;
    name: string;
    code: string;
    subject_id: number;
    description?: string;
    subject?: Subject;
}

export interface SubStrand {
    id: number;
    name: string;
    code: string;
    strand_id: number;
    description?: string;
    strand?: Strand;
}

// ============================================================================
// Assessment Types
// ============================================================================
export interface Assessment {
    id: number;
    title: string;
    description?: string;
    assessment_type_id: number;
    subject_id: number;
    class_id: number;
    teacher_id: number;
    academic_term_id: number;
    total_marks: number;
    passing_marks?: number;
    assessment_date: string;
    due_date?: string;
    status: 'draft' | 'published' | 'in_progress' | 'completed' | 'cancelled';
    assessment_type?: AssessmentType;
    subject?: Subject;
    class?: SchoolClass;
}

export interface AssessmentType {
    id: number;
    name: string;
    code: string;
    category: string;
    weight?: number;
    description?: string;
}

export interface StudentAssessment {
    id: number;
    student_id: number;
    assessment_id: number;
    marks_obtained?: number;
    percentage?: number;
    grade_level?: string;
    feedback?: string;
    is_absent: boolean;
    student?: Student;
    assessment?: Assessment;
}

export interface ReportCard {
    id: number;
    student_id: number;
    academic_term_id: number;
    class_id: number;
    total_marks?: number;
    average_percentage?: number;
    overall_grade?: string;
    class_rank?: number;
    stream_rank?: number;
    teacher_remarks?: string;
    principal_remarks?: string;
    status: 'draft' | 'approved' | 'published';
    student?: Student;
    academic_term?: AcademicTerm;
}

// ============================================================================
// Attendance Types
// ============================================================================
export interface Attendance {
    id: number;
    student_id: number;
    class_id: number;
    attendance_date: string;
    status: 'present' | 'absent' | 'late' | 'excused' | 'half_day';
    arrival_time?: string;
    departure_time?: string;
    reason?: string;
    notes?: string;
    student?: Student;
}

export interface AttendanceSummary {
    student_id: number;
    total_days: number;
    present_days: number;
    absent_days: number;
    late_days: number;
    excused_days: number;
    attendance_percentage: number;
}

// ============================================================================
// Timetable Types
// ============================================================================
export interface Timetable {
    id: number;
    class_id: number;
    academic_term_id: number;
    status: 'draft' | 'active' | 'archived';
    class?: SchoolClass;
}

export interface TimetableSlot {
    id: number;
    timetable_id: number;
    day_of_week: number;
    period_number: number;
    subject_id?: number;
    teacher_id?: number;
    room_id?: number;
    start_time: string;
    end_time: string;
    subject?: Subject;
    teacher?: Teacher;
}

// ============================================================================
// Finance Types
// ============================================================================
export interface FeeStructure {
    id: number;
    name: string;
    academic_year_id: number;
    class_id?: number;
    total_amount: number;
    status: 'active' | 'inactive';
    fee_items?: FeeItem[];
}

export interface FeeItem {
    id: number;
    fee_structure_id: number;
    fee_type_id: number;
    amount: number;
    is_mandatory: boolean;
    fee_type?: FeeType;
}

export interface FeeType {
    id: number;
    name: string;
    code: string;
    category: string;
    description?: string;
}

export interface Invoice {
    id: number;
    invoice_number: string;
    student_id: number;
    academic_term_id: number;
    total_amount: number;
    paid_amount: number;
    balance: number;
    due_date: string;
    status: 'pending' | 'partial' | 'paid' | 'overdue' | 'cancelled';
    student?: Student;
}

export interface Payment {
    id: number;
    receipt_number: string;
    invoice_id: number;
    amount: number;
    payment_method: string;
    payment_date: string;
    reference_number?: string;
    notes?: string;
    invoice?: Invoice;
}

// ============================================================================
// Communication Types
// ============================================================================
export interface Announcement {
    id: number;
    title: string;
    content: string;
    author_id: number;
    target_audience: string[];
    priority: 'low' | 'normal' | 'high' | 'urgent';
    published_at?: string;
    expires_at?: string;
    status: 'draft' | 'published' | 'archived';
    author?: User;
}

export interface Message {
    id: number;
    conversation_id: number;
    sender_id: number;
    content: string;
    read_at?: string;
    created_at: string;
    sender?: User;
}

export interface Notification {
    id: number;
    type: string;
    title: string;
    message: string;
    read_at?: string;
    created_at: string;
    data?: Record<string, any>;
}

// ============================================================================
// Event Types
// ============================================================================
export interface Event {
    id: number;
    title: string;
    description?: string;
    event_type: string;
    start_datetime: string;
    end_datetime: string;
    location?: string;
    is_all_day: boolean;
    status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
}

export interface Club {
    id: number;
    name: string;
    code: string;
    description?: string;
    category?: string;
    patron_id?: number;
    meeting_day?: string;
    meeting_time?: string;
    is_active: boolean;
    patron?: Teacher;
    members_count?: number;
}

// ============================================================================
// Dashboard & Analytics Types
// ============================================================================
export interface DashboardStats {
    total_students: number;
    total_teachers: number;
    total_classes: number;
    total_guardians: number;
    attendance_rate: number;
    fee_collection_rate: number;
    gender_distribution: {
        male: number;
        female: number;
    };
    enrollment_trends: {
        month: string;
        count: number;
    }[];
    class_performance: {
        class_name: string;
        average: number;
    }[];
    recent_activities: Activity[];
}

export interface Activity {
    id: number;
    description: string;
    type: string;
    timestamp: string;
    user?: User;
}

// ============================================================================
// Navigation Types (Extended)
// ============================================================================
export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
    badge?: string | number;
    children?: NavItem[];
    permission?: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
    permission?: string;
}

export interface BreadcrumbItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
}

// ============================================================================
// Table & Pagination Types
// ============================================================================
export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}

export interface TableColumn<T = any> {
    key: keyof T | string;
    label: string;
    sortable?: boolean;
    searchable?: boolean;
    formatter?: (value: any, row: T) => string;
    class?: string;
}

export interface TableFilter {
    key: string;
    label: string;
    type: 'text' | 'select' | 'date' | 'date_range' | 'number';
    options?: { value: string | number; label: string }[];
}

// ============================================================================
// Form Types
// ============================================================================
export interface SelectOption {
    value: string | number;
    label: string;
    disabled?: boolean;
}

export interface FormField {
    name: string;
    label: string;
    type: 'text' | 'email' | 'password' | 'number' | 'date' | 'select' | 'textarea' | 'checkbox' | 'radio' | 'file';
    placeholder?: string;
    required?: boolean;
    options?: SelectOption[];
    validation?: string;
}

// ============================================================================
// API Response Types
// ============================================================================
export interface ApiResponse<T = any> {
    status: 'success' | 'error';
    message?: string;
    data?: T;
    errors?: Record<string, string[]>;
}

// ============================================================================
// Chart Types
// ============================================================================
export interface ChartData {
    labels: string[];
    datasets: {
        label: string;
        data: number[];
        backgroundColor?: string | string[];
        borderColor?: string | string[];
        borderWidth?: number;
    }[];
}
