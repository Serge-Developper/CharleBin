describe('template spec', () => {
  it('passes', () => {
    cy.visit('https://example.cypress.io')
  });
});

it('test', function() {
  cy.visit('http://localhost:8000')
  cy.get('[name="message"]').type('test');
  cy.get('#passwordinput').click();
  cy.get('#passwordinput').type('test');
  cy.get('#sendbutton').click();
});