<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Option;
use App\Models\Section;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $basicSection = Section::where('section', 'Basic')->first();
        $selfFeedbackSection = Section::where('section', 'Self Feedback')->first();
        $officeFeedbackSection = Section::where('section', 'Office Feedback')->first();

        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Question::truncate();
        Option::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->createBasicQuestions($basicSection);
        $this->createSelfFeedbackQuestions($selfFeedbackSection);
        $this->createOfficeFeedbackQuestions($officeFeedbackSection);

        $this->command->info('Questions seeded successfully!');
    }

    private function createBasicQuestions($section)
    {
        $q1 = Question::create([
            'section_id' => $section->id,
            'question' => 'What is your current employment status?',
            'question_type' => 'multiple_choice_single',
            'text_response' => 'no_text',
        ]);
        $this->createOptions($q1->id, ['Full-time', 'Part-time', 'Contract', 'Intern']);

        $q2 = Question::create([
            'section_id' => $section->id,
            'question' => 'Which programming languages do you use? (Select all that apply)',
            'question_type' => 'checkbox_multiple',
            'text_response' => 'no_text',
        ]);
        $this->createOptions($q2->id, ['JavaScript', 'Python', 'Java', 'PHP', 'C++', 'Go']);

        Question::create([
            'section_id' => $section->id,
            'question' => 'Describe your role and responsibilities in brief.',
            'question_type' => 'text_only',
            'text_response' => 'required',
        ]);

        $q4 = Question::create([
            'section_id' => $section->id,
            'question' => 'How many years of experience do you have?',
            'question_type' => 'multiple_choice_single',
            'text_response' => 'optional',
        ]);
        $this->createOptions($q4->id, ['0-2 years', '3-5 years', '6-10 years', '10+ years']);

        $q5 = Question::create([
            'section_id' => $section->id,
            'question' => 'Which tools/technologies are you proficient in? Please elaborate.',
            'question_type' => 'checkbox_textarea',
            'text_response' => 'required',
        ]);
        $this->createOptions($q5->id, ['Git', 'Docker', 'Kubernetes', 'AWS', 'CI/CD', 'Databases']);

        $q6 = Question::create([
            'section_id' => $section->id,
            'question' => 'Do you prefer remote work or office work?',
            'question_type' => 'multiple_choice_single',
            'text_response' => 'no_text',
        ]);
        $this->createOptions($q6->id, ['Remote', 'Office', 'Hybrid', 'No preference']);

        Question::create([
            'section_id' => $section->id,
            'question' => 'Any additional comments about your work setup?',
            'question_type' => 'text_only',
            'text_response' => 'optional',
        ]);

        $q8 = Question::create([
            'section_id' => $section->id,
            'question' => 'What is your highest level of education? Please specify your field.',
            'question_type' => 'multiple_choice_single',
            'text_response' => 'required',
        ]);
        $this->createOptions($q8->id, ['High School', 'Bachelor\'s', 'Master\'s', 'PhD', 'Other']);

        $q9 = Question::create([
            'section_id' => $section->id,
            'question' => 'Which certifications do you hold? (Optional)',
            'question_type' => 'checkbox_multiple',
            'text_response' => 'no_text',
        ]);
        $this->createOptions($q9->id, ['AWS Certified', 'Google Cloud', 'Azure', 'Scrum Master', 'PMP', 'None']);

        $q10 = Question::create([
            'section_id' => $section->id,
            'question' => 'What is your preferred communication style?',
            'question_type' => 'multiple_choice_single',
            'text_response' => 'optional',
        ]);
        $this->createOptions($q10->id, ['Email', 'Slack/Chat', 'Video calls', 'In-person', 'Phone calls']);
    }

    private function createSelfFeedbackQuestions($section)
    {
        $q1 = Question::create([
            'section_id' => $section->id,
            'question' => 'How would you rate your overall performance this month?',
            'question_type' => 'multiple_choice_single',
            'text_response' => 'no_text',
        ]);
        $this->createOptions($q1->id, ['Excellent', 'Good', 'Average', 'Below Average', 'Poor']);

        Question::create([
            'section_id' => $section->id,
            'question' => 'What were your major achievements this month?',
            'question_type' => 'text_only',
            'text_response' => 'required',
        ]);

        $q3 = Question::create([
            'section_id' => $section->id,
            'question' => 'Which skills did you improve? Explain how.',
            'question_type' => 'checkbox_textarea',
            'text_response' => 'required',
        ]);
        $this->createOptions($q3->id, ['Technical Skills', 'Communication', 'Leadership', 'Problem Solving', 'Time Management']);

        $q4 = Question::create([
            'section_id' => $section->id,
            'question' => 'Did you face any challenges? Please describe.',
            'question_type' => 'multiple_choice_single',
            'text_response' => 'required',
        ]);
        $this->createOptions($q4->id, ['Yes, major challenges', 'Yes, minor challenges', 'No challenges', 'Prefer not to say']);

        $q5 = Question::create([
            'section_id' => $section->id,
            'question' => 'What areas do you want to focus on for improvement?',
            'question_type' => 'checkbox_multiple',
            'text_response' => 'no_text',
        ]);
        $this->createOptions($q5->id, ['Coding Skills', 'System Design', 'Testing', 'Documentation', 'Code Review', 'Mentoring']);

        Question::create([
            'section_id' => $section->id,
            'question' => 'What are your goals for next month?',
            'question_type' => 'text_only',
            'text_response' => 'required',
        ]);

        $q7 = Question::create([
            'section_id' => $section->id,
            'question' => 'How satisfied are you with your current projects?',
            'question_type' => 'multiple_choice_single',
            'text_response' => 'optional',
        ]);
        $this->createOptions($q7->id, ['Very Satisfied', 'Satisfied', 'Neutral', 'Dissatisfied', 'Very Dissatisfied']);

        $q8 = Question::create([
            'section_id' => $section->id,
            'question' => 'Which training programs would you like to attend?',
            'question_type' => 'checkbox_multiple',
            'text_response' => 'optional',
        ]);
        $this->createOptions($q8->id, ['Technical Workshops', 'Soft Skills', 'Leadership Training', 'Certification Courses', 'None']);

        $q9 = Question::create([
            'section_id' => $section->id,
            'question' => 'Do you feel you need additional support? Please explain.',
            'question_type' => 'multiple_choice_single',
            'text_response' => 'required',
        ]);
        $this->createOptions($q9->id, ['Yes, urgently', 'Yes, somewhat', 'No', 'Not sure']);

        Question::create([
            'section_id' => $section->id,
            'question' => 'Any other feedback or suggestions for self-improvement?',
            'question_type' => 'text_only',
            'text_response' => 'optional',
        ]);
    }

    private function createOfficeFeedbackQuestions($section)
    {
        $q1 = Question::create([
            'section_id' => $section->id,
            'question' => 'How would you rate the overall work environment?',
            'question_type' => 'multiple_choice_single',
            'text_response' => 'no_text',
        ]);
        $this->createOptions($q1->id, ['Excellent', 'Good', 'Average', 'Below Average', 'Poor']);

        $q2 = Question::create([
            'section_id' => $section->id,
            'question' => 'What aspects of the office culture do you appreciate? Explain why.',
            'question_type' => 'checkbox_textarea',
            'text_response' => 'required',
        ]);
        $this->createOptions($q2->id, ['Team Collaboration', 'Work-Life Balance', 'Management Support', 'Learning Opportunities', 'Recognition']);

        Question::create([
            'section_id' => $section->id,
            'question' => 'What improvements would you suggest for the office?',
            'question_type' => 'text_only',
            'text_response' => 'required',
        ]);

        $q4 = Question::create([
            'section_id' => $section->id,
            'question' => 'Are you satisfied with the communication from management? Please elaborate.',
            'question_type' => 'multiple_choice_single',
            'text_response' => 'required',
        ]);
        $this->createOptions($q4->id, ['Very Satisfied', 'Satisfied', 'Neutral', 'Dissatisfied', 'Very Dissatisfied']);

        $q5 = Question::create([
            'section_id' => $section->id,
            'question' => 'Which office facilities need improvement?',
            'question_type' => 'checkbox_multiple',
            'text_response' => 'no_text',
        ]);
        $this->createOptions($q5->id, ['Internet/WiFi', 'Meeting Rooms', 'Cafeteria', 'Parking', 'Workstations', 'Recreation Area']);

        $q6 = Question::create([
            'section_id' => $section->id,
            'question' => 'How often do you feel stressed at work?',
            'question_type' => 'multiple_choice_single',
            'text_response' => 'optional',
        ]);
        $this->createOptions($q6->id, ['Always', 'Often', 'Sometimes', 'Rarely', 'Never']);

        $q7 = Question::create([
            'section_id' => $section->id,
            'question' => 'What benefits would you like to see added?',
            'question_type' => 'checkbox_multiple',
            'text_response' => 'optional',
        ]);
        $this->createOptions($q7->id, ['Health Insurance', 'Gym Membership', 'Flexible Hours', 'Remote Work', 'Learning Budget', 'Stock Options']);

        Question::create([
            'section_id' => $section->id,
            'question' => 'How can the management better support your career growth?',
            'question_type' => 'text_only',
            'text_response' => 'required',
        ]);

        $q9 = Question::create([
            'section_id' => $section->id,
            'question' => 'Do you feel valued as a team member? Please explain.',
            'question_type' => 'multiple_choice_single',
            'text_response' => 'required',
        ]);
        $this->createOptions($q9->id, ['Strongly Agree', 'Agree', 'Neutral', 'Disagree', 'Strongly Disagree']);

        $q10 = Question::create([
            'section_id' => $section->id,
            'question' => 'What team activities would you like to participate in? Share your ideas.',
            'question_type' => 'checkbox_textarea',
            'text_response' => 'required',
        ]);
        $this->createOptions($q10->id, ['Team Outings', 'Sports Events', 'Hackathons', 'Workshops', 'Social Gatherings', 'Volunteer Work']);
    }

    private function createOptions($questionId, $options)
    {
        foreach ($options as $optionText) {
            Option::create([
                'question_id' => $questionId,
                'option_text' => $optionText,
            ]);
        }
    }
}
