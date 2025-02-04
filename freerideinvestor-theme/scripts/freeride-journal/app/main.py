# main.py

import json
import asyncio
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from chain_of_thought_reasoner import ChainOfThoughtReasoner
import logging

# Initialize logging
logging.basicConfig(filename='../logs/app.log', level=logging.DEBUG,
                    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

app = FastAPI()

# Define request schema
class TradeDetails(BaseModel):
    symbol: str
    entry_price: float
    exit_price: float
    strategy: str
    comments: Optional[str] = ""

class TradeJournalRequest(BaseModel):
    user_id: int
    trade_details: TradeDetails

# Initialize the reasoner (Replace MockAgentDispatcher with your actual implementation)
class MockAgentDispatcher:
    async def dispatch_task(self, task, agent_name, use_chain_of_thought=False):
        # Mock response for testing
        logger.debug(f"Mock dispatch_task called with task: {task}")
        return f"Processed task: {task}"

reasoner = ChainOfThoughtReasoner(agent_dispatcher=MockAgentDispatcher())

@app.post("/process_trade_journal")
async def process_trade_journal(request: TradeJournalRequest):
    task = f"Analyze this trade: {request.trade_details.dict()}"
    try:
        result = await reasoner.solve_task_with_reasoning(task, agent_name="tradeAgent")
        journal_entry = {
            "reasoning_steps": result.split('\n'),  # Adjust based on actual result format
            "evaluation": "Trade followed the strategy but lacked emotional discipline.",
            "recommendations": "Stick to predefined risk/reward and avoid overreacting to volatility."
        }
        logger.debug(f"Journal entry created: {journal_entry}")
        return {"status": "success", "journal_entry": journal_entry}
    except Exception as e:
        logger.error(f"Error processing trade journal: {e}")
        raise HTTPException(status_code=500, detail=str(e))
