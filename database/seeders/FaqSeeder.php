<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;

class FaqSeeder extends Seeder
{
    public function run()
    {
        $faqs = [
            ['question' => 'How does InstaHandi work?', 'answer' => 'InstaHandi connects homeowners with skilled professionals for various home services. Simply create a project, receive bids from pre-vetted service providers, and choose the best option based on your needs and budget.'],
            ['question' => 'Are the service providers on InstaHandi verified?', 'answer' => 'Yes, all service providers are rigorously tested and verified to ensure quality and reliability.'],
            ['question' => 'How do I get quotes for my project?', 'answer' => 'Just post your requirements and service providers will submit their bids. You can then choose the one that suits you best.'],
            ['question' => 'Is there a fee to use InstaHandi?', 'answer' => 'There are no fees for homeowners to post projects and receive quotes. Service providers pay a small fee to bid on projects.'],
            ['question' => 'What if I\'m not satisfied with the service provided?', 'answer' => 'InstaHandi offers a satisfaction guarantee and will work with you to resolve any issues to ensure you are satisfied with the outcome.']
        ];

        foreach ($faqs as $faq) {
            Faq::create([
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'admin_id' => 1
            ]);
        }
    }
}
