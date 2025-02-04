<?php
/**
 * Template Name: Education
 */
get_header(); ?>
    
<div class="container">
    <!-- 1. Hero Section -->
    <section id="hero" class="hero-section">
        <div class="container">
            <h1>Empower Your Trading Journey with Expert Education</h1>
            <p>Comprehensive courses and resources designed to elevate your trading skills.</p>
            <a href="#our-courses" class="btn btn-primary">Explore Courses</a>
        </div>
    </section>

    <!-- 2. Introduction -->
    <section id="introduction" class="introduction-section">
        <div class="container">
            <p>
                At FreeRideInvestor, we believe that knowledge is the cornerstone of successful trading. Our education services are meticulously crafted to provide traders of all levels with the insights, strategies, and tools needed to navigate the financial markets confidently. Whether you're just starting or looking to refine your skills, our comprehensive programs are designed to help you achieve your trading goals.
            </p>
        </div>
    </section>

    <!-- 3. Our Educational Offerings -->
    <section id="our-courses" class="our-courses-section">
        <div class="container">
            <h2>Our Educational Offerings</h2>

            <!-- Grid Container -->
            <div class="grid-container">
                <!-- Course 1: Introduction to Technical Analysis -->
                <div class="grid-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/team/placeholder-silhouette.png" alt="Introduction to Technical Analysis" class="course-image">
                    <h3>Introduction to Technical Analysis</h3>
                    <p><strong>Description:</strong> Learn the fundamentals of technical analysis to make informed trading decisions.</p>
                    
                    <p><strong>What You'll Learn:</strong></p>
                    <ul>
                        <li>Understanding chart patterns</li>
                        <li>Key technical indicators</li>
                        <li>Trend analysis</li>
                    </ul>
                    
                    <p><strong>Benefits:</strong></p>
                    <ul>
                        <li>Gain the ability to analyze market movements</li>
                        <li>Develop strategies based on technical data</li>
                        <li>Enhance your trading accuracy</li>
                    </ul>
                    
                    <p><strong>Who It's For:</strong> Beginners looking to build a solid foundation in technical analysis.</p>
                    
                    <a href="<?php echo site_url('/courses/introduction-to-technical-analysis'); ?>" class="btn btn-secondary">Learn More</a>
                </div>

                <!-- Course 2: Advanced Trading Strategies -->
                <div class="grid-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/team/placeholder-silhouette.png" alt="Advanced Trading Strategies" class="course-image">
                    <h3>Advanced Trading Strategies</h3>
                    <p><strong>Description:</strong> Dive deeper into sophisticated trading strategies to maximize your returns.</p>
                    
                    <p><strong>What You'll Learn:</strong></p>
                    <ul>
                        <li>Advanced charting techniques</li>
                        <li>Risk management strategies</li>
                        <li>Algorithmic trading principles</li>
                    </ul>
                    
                    <p><strong>Benefits:</strong></p>
                    <ul>
                        <li>Master complex trading strategies</li>
                        <li>Optimize your trading performance</li>
                        <li>Implement automated trading systems</li>
                    </ul>
                    
                    <p><strong>Who It's For:</strong> Experienced traders seeking to enhance their strategies and performance.</p>
                    
                    <a href="<?php echo site_url('/courses/advanced-trading-strategies'); ?>" class="btn btn-secondary">Learn More</a>
                </div>

                <!-- Placeholder for Future Courses -->
                <div class="grid-item coming-soon">
                    <h3>Coming Soon</h3>
                    <p>We're expanding our course offerings! Stay tuned for more exciting courses to enhance your trading journey.</p>
                </div>

                <div class="grid-item coming-soon">
                    <h3>Coming Soon</h3>
                    <p>New courses are on the way. Keep an eye out for updates and be the first to enroll!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Why Choose Our Education Programs -->
    <section id="why-choose-us" class="why-choose-us-section">
        <div class="container">
            <h2>Why Choose Our Education Programs</h2>
            <ul>
                <li><strong>Expert Instructors:</strong> Learn from seasoned traders with years of market experience.</li>
                <li><strong>Comprehensive Curriculum:</strong> Courses designed to cover all aspects of trading.</li>
                <li><strong>Flexible Learning:</strong> Self-paced and live session options to fit your schedule.</li>
                <li><strong>Practical Application:</strong> Hands-on exercises and real-world trading scenarios.</li>
                <li><strong>Supportive Community:</strong> Access to forums, mentorship, and peer support.</li>
            </ul>
            <p>
                Our education programs are tailored to meet the needs of traders at every level. Whether you're just starting or looking to deepen your trading knowledge, our expert-led courses provide the tools and insights necessary to succeed in the dynamic world of trading. With a focus on practical application and continuous support, we ensure that you not only learn but also effectively implement your knowledge in real trading environments.
            </p>
        </div>
    </section>

    <!-- 5. How It Works -->
    <section id="how-it-works" class="how-it-works-section">
        <div class="container">
            <h2>How It Works</h2>
            <ol>
                <li>
                    <h3>Step 1: Browse Courses</h3>
                    <p>Explore our diverse range of courses to find the ones that align with your trading goals and skill level.</p>
                </li>
                <li>
                    <h3>Step 2: Enroll</h3>
                    <p>Sign up for the courses that interest you. Our enrollment process is quick and straightforward.</p>
                </li>
                <li>
                    <h3>Step 3: Learn</h3>
                    <p>Access comprehensive course materials, participate in live sessions, and engage with our expert instructors.</p>
                </li>
                <li>
                    <h3>Step 4: Apply</h3>
                    <p>Implement the strategies and insights you've learned directly into your trading activities.</p>
                </li>
                <li>
                    <h3>Step 5: Achieve</h3>
                    <p>Track your progress, receive feedback, and continue your educational journey to achieve your trading objectives.</p>
                </li>
            </ol>
        </div>
    </section>

    <!-- 6. Success Stories -->
    <!-- Removed as per your request -->

    <!-- 7. Call to Action -->
    <section id="call-to-action" class="call-to-action-section">
        <div class="container" style="text-align: center;">
            <h2>Ready to Elevate Your Trading Skills?</h2>
            <a href="<?php echo site_url('/enroll'); ?>" class="btn btn-primary">Enroll in a Course Today</a>
        </div>
    </section>

    <!-- 8. FAQs -->
    <section id="faqs" class="faqs-section">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-item">
                <h3>Q: What courses do you offer?</h3>
                <p>A: We offer a variety of courses including Introduction to Technical Analysis, Advanced Trading Strategies, Risk Management Essentials, and Algorithmic Trading.</p>
            </div>
            <div class="faq-item">
                <h3>Q: How do I enroll?</h3>
                <p>A: Simply browse our courses, select the one you're interested in, and click the "Enroll Now" button to get started.</p>
            </div>
            <div class="faq-item">
                <h3>Q: What is the cost of the courses?</h3>
                <p>A: Our courses vary in price depending on the depth and duration. Please visit the individual course pages for detailed pricing information.</p>
            </div>
            <div class="faq-item">
                <h3>Q: Are there any prerequisites?</h3>
                <p>A: Most of our courses are designed for traders of all levels. However, some advanced courses may require prior knowledge or experience.</p>
            </div>
            <div class="faq-item">
                <h3>Q: Can I access the courses at my own pace?</h3>
                <p>A: Yes, we offer both self-paced and live session options to accommodate your schedule and learning preferences.</p>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>
