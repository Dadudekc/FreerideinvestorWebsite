import pytest
from unittest.mock import AsyncMock
from app.chain_of_thought_reasoner import ChainOfThoughtReasoner

@pytest.mark.asyncio
async def test_decompose_task_success():
    # Mock the agent dispatcher and Ollama CLI response
    mock_dispatcher = AsyncMock()
    mock_dispatcher.dispatch_task = AsyncMock(return_value='["Step 1", "Step 2"]')

    reasoner = ChainOfThoughtReasoner(agent_dispatcher=mock_dispatcher)
    reasoner._call_ollama_cli = AsyncMock(return_value='["Step 1", "Step 2"]')

    steps = await reasoner.decompose_task("Analyze the market trends for Q4.")
    assert steps == ["Step 1", "Step 2"]

@pytest.mark.asyncio
async def test_execute_step_success():
    mock_dispatcher = AsyncMock()
    mock_dispatcher.dispatch_task = AsyncMock(return_value="Step result")
    
    reasoner = ChainOfThoughtReasoner(agent_dispatcher=mock_dispatcher)
    reasoner.reasoning_graph.add_node("step_1", content="Test Step")
    
    await reasoner.execute_step("step_1", "default_agent")
    assert "step_1" in reasoner.memory
    assert reasoner.memory["step_1"] == "Step result"

@pytest.mark.asyncio
async def test_execute_step_failure():
    mock_dispatcher = AsyncMock()
    mock_dispatcher.dispatch_task = AsyncMock(return_value=None)
    
    reasoner = ChainOfThoughtReasoner(agent_dispatcher=mock_dispatcher)
    reasoner.reasoning_graph.add_node("step_1", content="Test Step")
    
    with pytest.raises(Exception) as excinfo:
        await reasoner.execute_step("step_1", "default_agent")
    assert "failed to execute" in str(excinfo.value)
