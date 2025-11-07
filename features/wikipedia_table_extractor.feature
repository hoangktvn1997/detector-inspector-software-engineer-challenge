Feature: Wikipedia Table Data Extractor and Graph Generator
  As a user
  I want to extract numeric data from a Wikipedia table
  So that I can visualize it as a graph

  Scenario: Extract and plot numeric column from Wikipedia page
    Given I have a Wikipedia URL "https://en.wikipedia.org/wiki/Women%27s_high_jump_world_record_progression"
    When I run the extractor command with the URL
    Then the program should fetch the Wikipedia page
    And the program should find at least one table
    And the program should identify a numeric column in the table
    And the program should extract numeric values from that column
    And the program should generate a graph image file
    And the output image file should exist

  Scenario: Handle invalid URL
    Given I have an invalid URL "https://invalid-url-example.com"
    When I run the extractor command with the URL
    Then the program should handle the error gracefully
    And display an appropriate error message

  Scenario: Handle Wikipedia page without tables
    Given I have a Wikipedia URL that contains no tables
    When I run the extractor command with the URL
    Then the program should detect that no tables exist
    And display an appropriate error message

