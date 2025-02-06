import pygame
import random

# Constants
WIDTH, HEIGHT = 800, 600
TILE_SIZE = 50
GRID_WIDTH, GRID_HEIGHT = WIDTH // TILE_SIZE, HEIGHT // TILE_SIZE
WHITE = (255, 255, 255)
RED = (255, 0, 0)
BLUE = (0, 0, 255)

# Load Images
player_icon = pygame.image.load("player.png")
enemy_icon = pygame.image.load("enemy.png")
player_icon = pygame.transform.scale(player_icon, (TILE_SIZE, TILE_SIZE))
enemy_icon = pygame.transform.scale(enemy_icon, (TILE_SIZE, TILE_SIZE))

# Initialize Pygame
pygame.init()
screen = pygame.display.set_mode((WIDTH, HEIGHT))
clock = pygame.time.Clock()

# Character Class
class Character:
    def __init__(self, x, y, image, team):
        self.x, self.y = x, y
        self.image = image
        self.team = team  # 'player' or 'enemy'
        self.hp = 10
        self.move_range = 3
        self.attack_range = 1
        self.alive = True

    def draw(self):
        if self.alive:
            screen.blit(self.image, (self.x * TILE_SIZE, self.y * TILE_SIZE))

    def move(self, new_x, new_y):
        if abs(new_x - self.x) + abs(new_y - self.y) <= self.move_range:
            self.x, self.y = new_x, new_y

    def attack(self, target):
        if abs(target.x - self.x) + abs(target.y - self.y) <= self.attack_range:
            target.hp -= 5
            if target.hp <= 0:
                target.alive = False

# Game Setup
player = Character(2, 2, player_icon, 'player')
enemy = Character(6, 6, enemy_icon, 'enemy')
turn = 'player'

def enemy_ai():
    """Simple AI: Move closer and attack if in range."""
    if enemy.alive:
        dx, dy = player.x - enemy.x, player.y - enemy.y
        if abs(dx) + abs(dy) <= enemy.attack_range:
            enemy.attack(player)
        else:
            move_x, move_y = enemy.x + (1 if dx > 0 else -1 if dx < 0 else 0), enemy.y + (1 if dy > 0 else -1 if dy < 0 else 0)
            enemy.move(move_x, move_y)

def draw_grid():
    for x in range(0, WIDTH, TILE_SIZE):
        pygame.draw.line(screen, WHITE, (x, 0), (x, HEIGHT))
    for y in range(0, HEIGHT, TILE_SIZE):
        pygame.draw.line(screen, WHITE, (0, y), (WIDTH, y))

# Game Loop
running = True
while running:
    screen.fill((0, 0, 0))
    draw_grid()
    player.draw()
    enemy.draw()

    for event in pygame.event.get():
        if event.type == pygame.QUIT:
            running = False
        elif event.type == pygame.KEYDOWN and turn == 'player':
            new_x, new_y = player.x, player.y
            if event.key == pygame.K_LEFT:
                new_x -= 1
            elif event.key == pygame.K_RIGHT:
                new_x += 1
            elif event.key == pygame.K_UP:
                new_y -= 1
            elif event.key == pygame.K_DOWN:
                new_y += 1
            elif event.key == pygame.K_SPACE:
                player.attack(enemy)
            
            if 0 <= new_x < GRID_WIDTH and 0 <= new_y < GRID_HEIGHT:
                player.move(new_x, new_y)
            turn = 'enemy'
    
    if turn == 'enemy':
        enemy_ai()
        turn = 'player'
    
    pygame.display.flip()
    clock.tick(30)

pygame.quit()
